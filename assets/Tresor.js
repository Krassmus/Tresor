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
        openpgp.encrypt(options).then(function(ciphertext) {
            console.log(ciphertext);
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

                options = {
                    message: message,  // parse armored message
                    privateKey: my_key // for decryption
                };
                openpgp.decrypt(options).then(function (plaintext) {
                    jQuery("#content").val(plaintext.data);
                    return plaintext.data; // 'Hello, World!'
                }, function (error) {
                    jQuery("#encryption_error").show("fade");
                    jQuery("#content_form").hide();
                });
            }
        }
    },
    askForPassphrase: function (wrong) {
        sessionStorage.setItem("STUDIP.Tresor.passphrase", "");
        jQuery("#question_passphrase").dialog({
            title: jQuery("#question_passphrase_title").text(),
            modal: true
        });
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
