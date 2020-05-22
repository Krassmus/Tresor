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
                var new_time = new Date();

                let text = ciphertext.data.replace(/\r/, "");

                let b = new File(
                    [text],
                    file.name,
                    {
                        type: file.type
                    }
                );
                STUDIP.Tresor.uploadEncryptedFile(
                    b,
                    jQuery("#uploadform [name=container_id]").val() || "",
                    function () {

                    }
                ).then(function () {
                    location.reload();
                });

            });
        };
        reader.readAsDataURL(file);
    },
    uploadEncryptedFile: function (file, container_id) {
        let data = new FormData();
        data.append(`file`, file, file.name.normalize());

        let prom = new Promise(function (resolve, reject) {
            var request = new XMLHttpRequest();
            request.open('POST', STUDIP.URLHelper.getURL(`plugins.php/tresor/container/upload/` + container_id));
            request.addEventListener('loadend', function (event) {
                resolve(JSON.parse(request.response));
            });
            request.send(data);
        });
        return prom;
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
            let text = ciphertext.data.replace(/\r/, "");
            let b = new File(
                [text],
                $("#content_form input[name=name]").val(),
                {
                    type: $("#content_form input[name=mime_type]").val()
                }
            );
            STUDIP.Tresor.uploadEncryptedFile(
                b,
                jQuery("#content_form [name=container_id]").val() || ""
            ).then(function () {
                location.reload();
            });
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
                    window.setTimeout(function () {
                        jQuery("#content").val(plaintext.data);
                        if (jQuery("#tresor_decrypted_preview").hasClass("prevent_download")
                            && jQuery("#content_form input[name=mime_type]").val() === "application/pdf") {
                            let iframe = document.getElementById("tresor_decrypted_preview");
                            let binary = atob(plaintext.data.substr(plaintext.data.indexOf(",") + 1));
                            let rawLength = binary.length;
                            let array = new Uint8Array(new ArrayBuffer(binary.length));
                            for (var i = 0; i < binary.length; i++) {
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
                    }, 150);
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
            if (!jQuery("#question_passphrase").is(".ui-dialog #question_passphrase")) {
                jQuery("#question_passphrase").dialog({
                    title: jQuery("#question_passphrase_title").text(),
                    modal: true,
                    width: 400,
                    classes: {
                        "ui-dialog": "front"
                    }
                });
            }
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
                    if (!containers.length) {
                        alert("Nichts zu tun.");
                        return;
                    }
                    let number = containers.length;
                    let finished = 0;
                    jQuery("#dialog_wait_renew_containers").dialog({
                        "modal": true,
                        "title": jQuery("#dialog_wait_renew_containers").data("title")
                    });
                    var keys = [];
                    for (var i in STUDIP.Tresor.keyToEncryptFor) {
                        var publicKey = openpgp.key.readArmored(STUDIP.Tresor.keyToEncryptFor[i]);
                        keys.push(publicKey.keys[0]);
                    }
                    for (let i in containers) {
                        if (containers[i].encrypted_content) {
                            STUDIP.Tresor.decryptText(containers[i].encrypted_content).then(function (plaintext) {
                                var options = {
                                    data: plaintext,     // input as String (or Uint8Array)
                                    publicKeys: keys,  // for encryption
                                };
                                openpgp.encrypt(options).then(function (ciphertext) {
                                    finished++;
                                    jQuery(".uploadbar").css("background-size", Math.floor(100 * finished / (2 * number)) + "% 100%");

                                    let text = ciphertext.data.replace(/\r/, "");

                                    let b = new File(
                                        [text],
                                        containers[i].name,
                                        {
                                            type: containers[i].mime_type
                                        }
                                    );
                                    STUDIP.Tresor.uploadEncryptedFile(
                                        b,
                                        containers[i].tresor_id
                                    ).then(function () {
                                        finished++;
                                        jQuery(".uploadbar").css("background-size", Math.floor(100 * finished / (2 * number)) + "% 100%");
                                        if (finished === 2 * number) {
                                            location.reload();
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
