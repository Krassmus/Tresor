STUDIP.Tresor = {
    createUserKeys: function () {
        jQuery("#set_password").dialog({
            modal: true,
            title: jQuery("#set_password_title").text(),
            width: 400
        });
    },
    setPassword: function () {
        if (jQuery("#tresor_password").val().length < 10) {
            alert("Passwort ist kleiner als 10 Zeichen. Etwas sicherer sollte es schon sein.");
            return;
        }
        if (jQuery("#tresor_password").val() !== jQuery("#tresor_password_2").val()) {
            alert("Passwort nicht gleich.");
            return;
        }

        jQuery("#wheel img").addClass("spinning").removeClass("notpinning");
        var options = {
            userIds: [{
                name: jQuery("#set_password input[name=user]").val(),
                email: jQuery("#set_password input[name=mail]").val()
            }],
            numBits: 4096, // RSA key size
            passphrase: jQuery("#tresor_password").val() // protects the private key
        };

        openpgp.generateKey(options).then(function(key) {
            var private_key = key.privateKeyArmored.replace(/\r/, "");
            var public_key = key.publicKeyArmored.replace(/\r/, "");
            sessionStorage.setItem('STUDIP.Tresor.passphrase', jQuery("#tresor_password").val());
            //jQuery("#wheel img").addClass("notpinning").removeClass("spinning");

            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/tresor/userdata/set_keys",
                type: "post",
                data: {
                    'private_key': private_key, // '-----BEGIN PGP PRIVATE KEY BLOCK ... '
                    'public_key' : public_key, // '-----BEGIN PGP PUBLIC KEY BLOCK ... '
                },
                success: function (message_box) {
                    jQuery("#my_key").data("private_key", private_key).data("public_key", public_key);
                    location.reload();
                }
            });
        });
    },
    selectFile: function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function(event) {
            var file_source = event.target.result;

            var keys = [];
            for (var i in STUDIP.Tresor.keyToEncryptFor) {
                var publicKey = openpgp.key.readArmored(STUDIP.Tresor.keyToEncryptFor[i]);
                keys.push(publicKey.keys[0]);
            }

            var options = {
                data: file_source,     // input as String (or Uint8Array)
                publicKeys: keys,  // for encryption
            };
            var time = new Date();
            openpgp.encrypt(options).then(function(ciphertext) {
                jQuery("#encrypted_content").val(ciphertext.data.replace(/\r/, "")); // '-----BEGIN PGP MESSAGE ... END PGP MESSAGE-----'
                jQuery("#content_form [name=name]").val(file.name);
                jQuery("#content_form [name=mime_type]").val(file.type);
                jQuery("#content_form").addClass("file").removeClass("text");
                jQuery("#content").val("");

                var new_time = new Date();
                jQuery("#encrypted_content").closest("form").submit();
            });

        };
        reader.readAsDataURL(file);
    },
    uploadFile: function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function(event) {
            var file_source = event.target.result;

            var keys = [];
            for (var i in STUDIP.Tresor.keyToEncryptFor) {
                var publicKey = openpgp.key.readArmored(STUDIP.Tresor.keyToEncryptFor[i]);
                keys.push(publicKey.keys[0]);
            }

            var options = {
                data: file_source,     // input as String (or Uint8Array)
                publicKeys: keys,  // for encryption
            };
            var time = new Date();
            openpgp.encrypt(options).then(function(ciphertext) {
                jQuery("#uploadform input[name=encrypted_content]").val(ciphertext.data.replace(/\r/, "")); // '-----BEGIN PGP MESSAGE ... END PGP MESSAGE-----'
                jQuery("#uploadform [name=name]").val(file.name);
                jQuery("#uploadform [name=mime_type]").val(file.type);
                jQuery("#content").val("");

                var new_time = new Date();
                jQuery("#uploadform").submit();
            });
        };
        reader.readAsDataURL(file);
    },
    downloadFile: function () {
        if (jQuery("#encrypted_content").val()) {
            var passphrase = sessionStorage.getItem("STUDIP.Tresor.passphrase");
            if (!passphrase) {
                STUDIP.Tresor.askForPassphrase(false);
            } else {
                var my_key = jQuery("#my_key").data("private_key");
                my_key = openpgp.key.readArmored(my_key);
                my_key = my_key.keys[0];
                var success = my_key.decrypt(passphrase);
                if (!success) {
                    //ask for passphrase:
                    STUDIP.Tresor.askForPassphrase(false);
                    return;
                }
                var message = openpgp.message.readArmored(jQuery("#encrypted_content").val());

                var options = {
                    message: message,  // parse armored message
                    privateKey: my_key // for decryption
                };
                openpgp.decrypt(options).then(function (plaintext) {
                    var element = document.createElement('a');
                    element.setAttribute('href', plaintext.data);
                    element.setAttribute('download', jQuery("#content_form [name=name]").val());
                    element.style.display = 'none';
                    document.body.appendChild(element);
                    element.click();
                    document.body.removeChild(element);

                    return plaintext.data;
                }, function (error) {
                    jQuery("#encryption_error").show("fade");
                    jQuery("#content_form").hide();
                });
            }
        }
    },
    selectText: function () {
        jQuery("#content").val("").attr("placeholder", "Text eingeben");
        jQuery("#content_form [name=mime_type]").val("text/plain")
        jQuery("#content_form").removeClass("file").addClass("text");
    },
    storeContainer: function () {
        var content = jQuery("#content").val();

        var keys = [];
        for (var i in STUDIP.Tresor.keyToEncryptFor) {
            var publicKey = openpgp.key.readArmored(STUDIP.Tresor.keyToEncryptFor[i]);
            keys.push(publicKey.keys[0]);
        }

        var options = {
            data: content,     // input as String (or Uint8Array)
            publicKeys: keys,  // for encryption
        };
        openpgp.encrypt(options).then(function (ciphertext) {
            jQuery("#encrypted_content").val(ciphertext.data.replace(/\r/, "")); // '-----BEGIN PGP MESSAGE ... END PGP MESSAGE-----'
            jQuery("#encrypted_content").closest("form").submit();
        });
    },

    decryptContainer: function () {
        if (jQuery("#encrypted_content").val()) {
            var passphrase = sessionStorage.getItem("STUDIP.Tresor.passphrase");
            if (!passphrase) {
                STUDIP.Tresor.askForPassphrase(false);
            } else {
                var my_key = jQuery("#my_key").data("private_key");
                my_key = openpgp.key.readArmored(my_key);
                my_key = my_key.keys[0];
                var success = my_key.decrypt(passphrase);
                if (!success) {
                    //ask for passphrase:
                    STUDIP.Tresor.askForPassphrase(false);
                    return;
                }
                var message = openpgp.message.readArmored(jQuery("#encrypted_content").val());

                var options = {
                    message: message,  // parse armored message
                    privateKey: my_key // for decryption
                };
                openpgp.decrypt(options).then(function (plaintext) {
                    jQuery("#content").val(plaintext.data);
                    if (jQuery("#tresor_decrypted_preview").hasClass("prevent_download")
                            && jQuery("#content_form input[name=mime_type]").val() === "application/pdf") {
                        let iframe = document.getElementById("tresor_decrypted_preview");
                        let binary = atob(plaintext.data.substr(plaintext.data.indexOf(",") + 1));
                        let rawLength = binary.length;
                        let array = new Uint8Array(new ArrayBuffer(binary.length));
                        for(var i = 0; i < binary.length; i++) {
                            array[i] = binary.charCodeAt(i);
                        }

                        if (iframe.contentWindow.PDFViewerApplication) {
                            iframe.contentWindow.PDFViewerApplication.open(
                                new Uint8Array(array)
                            );
                        } else {
                            iframe.onload = function () {
                                iframe.contentWindow.PDFViewerApplication.open(
                                    new Uint8Array(array)
                                );
                            };
                        }
                    } else {
                        jQuery("#tresor_decrypted_preview").attr("src", plaintext.data);
                    }
                    return plaintext.data;
                }, function (error) {
                    jQuery("#encryption_error").show("fade");
                    jQuery("#content_form").hide();
                });
            }
        }
    },
    decryptText: function (message) {
        if (message) {
            var passphrase = sessionStorage.getItem("STUDIP.Tresor.passphrase");
            if (!passphrase) {
                STUDIP.Tresor.askForPassphrase(false);
            } else {

                var my_key = jQuery("#my_key").data("private_key");
                my_key = openpgp.key.readArmored(my_key);
                my_key = my_key.keys[0];
                var success = my_key.decrypt(passphrase);
                if (!success) {
                    //ask for passphrase:
                    STUDIP.Tresor.askForPassphrase(false);
                    return;
                }
                if (!message) {
                    message = openpgp.message.readArmored(jQuery("#encrypted_content").val());
                } else {
                    message = openpgp.message.readArmored(message);
                }


                var options = {
                    message: message,  // parse armored message
                    privateKey: my_key // for decryption
                };


                return openpgp.decrypt(options).then(function (plaintext) {
                    jQuery("#content").val(plaintext.data);
                    return plaintext.data;
                }, function (error) {
                    jQuery("#encryption_error").show("fade");
                    jQuery("#content_form").hide();
                });
            }
        }
    },
    askForPassphrase: function (wrong) {
        sessionStorage.setItem("STUDIP.Tresor.passphrase", "");
        window.setTimeout(function () {
            jQuery("#question_passphrase [name=passphrase]").val("");
            jQuery("#question_passphrase .wrong").hide();
            jQuery("#question_passphrase").dialog({
                title: jQuery("#question_passphrase_title").text(),
                modal: true,
                width: 400,
                classes: {
                    "ui-dialog": "front"
                }
            });
            jQuery("#question_passphrase [name=passphrase]").focus();
            if (wrong) {
                jQuery("#question_passphrase .wrong").show("fade");
            }
        }, 150);
    },

    extractPrivateKey: function () {
        var passphrase = jQuery("#question_passphrase [name=passphrase]").val();
        //Private Key
        var my_key = jQuery("#my_key").data("private_key");
        my_key = openpgp.key.readArmored(my_key);
        my_key = my_key.keys[0];
        var success = my_key.decrypt(passphrase);
        if (!success) {
            //ask for passphrase:
            STUDIP.Tresor.askForPassphrase(true);
            return;
        } else {
            sessionStorage.setItem("STUDIP.Tresor.passphrase", passphrase);
            jQuery("#question_passphrase").dialog("close");
            STUDIP.Tresor.decryptContainer();
        }
    },

    updateEncryption: function () {
        if (window.confirm("Wirklich aktualisieren?")) {
            let course_id = STUDIP.URLHelper.parameters.cid;
            jQuery.ajax({
                "url": STUDIP.URLHelper.getURL("plugins.php/tresor/container/get_updatable_for_course/" + course_id),
                "dataType": "json",
                "success": function (containers) {
                    var keys = [];
                    for (var i in STUDIP.Tresor.keyToEncryptFor) {
                        var publicKey = openpgp.key.readArmored(STUDIP.Tresor.keyToEncryptFor[i]);
                        keys.push(publicKey.keys[0]);
                    }
                    let number = containers.length;
                    let finished = 0;
                    for (let i in containers) {
                        if (containers[i].encrypted_content) {
                            STUDIP.Tresor.decryptText(containers[i].encrypted_content).then(function (plaintext) {
                                var options = {
                                    data: plaintext,     // input as String (or Uint8Array)
                                    publicKeys: keys,  // for encryption
                                };
                                openpgp.encrypt(options).then(function (ciphertext) {
                                    let encrypted_content = ciphertext.data.replace(/\r/, "");
                                    jQuery.ajax({
                                        "url": STUDIP.URLHelper.getURL("plugins.php/tresor/container/update/" + containers[i].tresor_id),
                                        "data": {
                                            "encrypted_content": encrypted_content
                                        },
                                        "type": "post",
                                        "success": function () {
                                            finished++;
                                            if (finished === number) {
                                                location.reload();
                                            }
                                        }
                                    });

                                });
                            }, function (error) {
                            });
                        }
                    }
                }
            });
        }
    }


};
