STUDIP.Tresor = {
    createUserKeys: function () {
        jQuery("#set_password").dialog({
            modal: true,
            title: jQuery("#set_password_title").text(),
            width: 400
        });
    },
    setPassword: function () {
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
            numBits: 2048, // RSA key size
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
            console.log(options);
            var time = new Date();
            openpgp.encrypt(options).then(function(ciphertext) {
                jQuery("#encrypted_content").val(ciphertext.data.replace(/\r/, "")); // '-----BEGIN PGP MESSAGE ... END PGP MESSAGE-----'
                jQuery("#content_form [name=name]").val(file.name);
                jQuery("#content_form [name=mime_type]").val(file.type);
                jQuery("#content_form").addClass("file").removeClass("text");
                jQuery("#content").val("");

                console.log(file.type);
                var new_time = new Date();
                console.log(new_time - time);
                jQuery("#encrypted_content").closest("form").submit();
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
                console.log(jQuery("#encrypted_content").val());
                my_key = openpgp.key.readArmored(my_key);
                my_key = my_key.keys[0];
                var success = my_key.decrypt(passphrase);
                if (!success) {
                    //ask for passphrase:
                    STUDIP.Tresor.askForPassphrase(false);
                    return;
                }
                var message = openpgp.message.readArmored(jQuery("#encrypted_content").val());

                options = {
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

                    //jQuery("#content").val(plaintext.data);
                    return plaintext.data;
                }, function (error) {
                    jQuery("#encryption_error").show("fade");
                    jQuery("#content_form").hide();
                });
            }
        }
    },
    selectText: function() {
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
                console.log(jQuery("#encrypted_content").val());
                my_key = openpgp.key.readArmored(my_key);
                my_key = my_key.keys[0];
                var success = my_key.decrypt(passphrase);
                if (!success) {
                    //ask for passphrase:
                    STUDIP.Tresor.askForPassphrase(false);
                    return;
                }
                var message = openpgp.message.readArmored(jQuery("#encrypted_content").val());

                options = {
                    message: message,  // parse armored message
                    privateKey: my_key // for decryption
                };
                openpgp.decrypt(options).then(function (plaintext) {
                    console.log(plaintext);
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
        jQuery("#question_passphrase [name=passphrase]").val("");
        jQuery("#question_passphrase .wrong").hide();
        jQuery("#question_passphrase").dialog({
            title: jQuery("#question_passphrase_title").text(),
            modal: true,
            width: 400
        });
        jQuery("#question_passphrase [name=passphrase]").focus();
        if (wrong) {
            jQuery("#question_passphrase .wrong").show("fade");
        }
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
    }
};
