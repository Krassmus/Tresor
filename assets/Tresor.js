STUDIP.Tresor = {
    createUserKeys: function () {
        jQuery("#set_password").fadeIn(300);
    },
    setPassword: function () {
        var crypto = window.crypto || window.msCrypto || window.webkitCrypto;
        var algorithmKeyGen = {
            name: "RSA-OAEP",
            modulusLength: 2048,
            publicExponent: new Uint8Array([0x01, 0x00, 0x01]),
            hash: {name: "SHA-256"}
        };
        var methods = ["encrypt", "decrypt"];
        crypto.subtle.generateKey(algorithmKeyGen, true, methods).then(function (keypair) {
            console.log(keypair);
        });
    },
    storeContainer: function () {
        var content = jQuery("#content").val();
        //use aes-group-key to encrypt the content
        var encrypted_content = "";
        jQuery("#encrypted_content").val(encrypted_content);
        jQuery("#encrypted_content").closest("form").submit();
    }
};

jQuery(function () {

});