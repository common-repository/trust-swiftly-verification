function isEmailValid(email) {
    var pattern = new RegExp(
        /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[0-9a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i
    );
    // eslint-disable-line max-len

    return pattern.test(email);
}
(function ($) {
    // Custom code here

    $(function () {
        window.TSVerification = {
            config: {
                userEmail: TSCheckoutConfig.user_email,
                ajaxUrl: TSCheckoutConfig.ajax_url,
                nonce: TSCheckoutConfig.nonce,
                verifyLinkText: TSCheckoutConfig.verify_link_text,
                thankyouPage: TSCheckoutConfig.thank_you_page,
            },

            $billingEmail: null,
            $submitBtn: null,

            init: function () {
                this.thankyouPage = TSCheckoutConfig.thank_you_page;
                this.userEmail = TSCheckoutConfig.user_email;

                if (this.thankyouPage !== "1") {
                    if (jQuery("#billing_email").length !== 0) {
                        this.$billingEmail = $("#billing_email");
                    } else {
                        if (jQuery("#email").length !== 0) {
                            this.$billingEmail = $("#email");
                        } else {
                            return 0;
                        }
                    }

                    if (!this.$billingEmail.val()) {
                        let img2 = "'" + TSCheckoutConfig.btn_img + "'";
                        $checkBtn = $(
                            '<span class="ts_emb_verify_temp">' +
                                '<div class="row" style="margin-top: 20px"> ' +
                                '<div class="col-6" style="margin-top: 20px"> ' +
                                '<div class="row" style="margin-top: 20px"> ' +
                                '<p style="text-align:center;"><u>Input a Email to verify</u></p> <button id="ts-verify-link" disabled class="btn ts-verify-link col-8" style="border-radius:20px;opacity: 0.5;background-image: url(' +
                                img2 +
                                ');margin: auto"></button>' +
                                "</div></div></div></span>"
                        );
                        this.$billingEmail.after($checkBtn);
                    }

                    if (jQuery("#email").length !== 0) {
                        $("#email").on(
                            "change",
                            this.maybeVerifyUser.bind(this)
                        );
                    } else {
                        if (jQuery("#billing_email").length !== 0) {
                            $("#billing_email").on(
                                "change",
                                this.maybeVerifyUser.bind(this)
                            );
                        } else {
                            return 0;
                        }
                    }

                    this.verifyUser(this.$billingEmail.val());
                    
                } else {
                    this.verifyUser(this.userEmail);
                }

                // this.initCheckoutFormSubmit();
            },

            // initCheckoutFormSubmit: function() {
            //     $(document).on('submit', '.woocommerce-checkout form', function(e) {
            //         this.checkVerify();
            //         console.log(123);
            //         return false;
            //     });
            // },

            maybeVerifyUser: function (e) {
                var email = e.target.value;
                this.verifyUser(email);
            },

            verifyUser: function (email) {
                if (!email || !this.isEmailValid(email)) {
                    $("#ts-verify-link").remove();
                    $(".ts_emb_verify_temp").remove();
                    $(".ts_emb_verify").remove();
                    return;
                }

                $('.ts_verification_successful').remove();
                $("#ts-verify-link").remove();
                $(".ts_emb_verify_temp").remove();
                $(".ts_emb_verify").remove();
                $(".ts-loader").remove();

                var sURL = jQuery("#sURL").val();
                var sSpinImage =
                    '<img class="ts-loader" src="' +
                    sURL +
                    '/wp-includes/images/wpspin.gif">';
                // this.$billingEmail.after(sSpinImage)
                $("#verify_div").append(sSpinImage);
                let is_verified = 0;
                let urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has("is_verified")) {
                    if (urlParams.get("is_verified") === "1") {
                        is_verified = 1;
                    }
                }

                if (TSCheckoutConfig.payment_method == "unknown") {
                    TSCheckoutConfig.payment_method = jQuery(
                        "input[name=payment_method]:checked"
                    ).val();
                }
                if (
                    TSCheckoutConfig.payment_method == "unknown" ||
                    typeof TSCheckoutConfig.payment_method === "undefined"
                ) {
                    if (
                        jQuery(
                            "input[name=radio-control-wc-payment-method-options]:checked"
                        ).length !== 0
                    ) {
                        TSCheckoutConfig.payment_method = jQuery(
                            "input[name=radio-control-wc-payment-method-options]:checked"
                        ).val();
                    }
                }

                $.post(
                    this.config.ajaxUrl + location.search,
                    {
                        action: "ts_get_user_verification",
                        nonce: this.config.nonce,
                        email: email,
                        payment_method: TSCheckoutConfig.payment_method,
                        thank_you_page: this.thankyouPage,
                    },
                    function (resp) {
                        if (!resp.success) {
                            $(".ts_emb_verify").hide();
                            return;
                        }

                        if (resp.data.type == "ok") {
                            var method = resp.data.method;
                            let pre1 = "";
                            let pre2 = "";
                            if (this.thankyouPage === "1") {
                                pre1 =
                                    '<div class="col-8" style="margin-top: 20px"> ' +
                                    '<div class="row" style="margin-top: 20px"> ';
                                pre2 = "</div></div>";
                            }
                            if (method == "modal") {
                                if (is_verified === 1) {
                                    let $checkBtn;
                                    $checkBtn = $(
                                        '<span class="ts_emb_verify">' +
                                            '<div class="row" style="margin-top: 20px"> ' +
                                            pre1 +
                                            resp.data.html +
                                            '<i class="fa fa-hourglass col-1" style="margin: auto;color:orange"></i>' +
                                            '<button id="ts-verify-check-button" class="ts-check-verify btn btn-small col-3" type="button">' +
                                            '<i class="fa fa-refresh"></i></button>' +
                                            pre2 +
                                            "</div></span>"
                                    );
                                    if (this.thankyouPage !== "1") {
                                        this.$billingEmail.after($checkBtn);
                                    } else {
                                        $("#verify_div").html($checkBtn);
                                    }
                                    $("#trustVerify").addClass("col-8");
                                    $("#ts-verify-check-button").on(
                                        "click",
                                        this.checkVerify.bind(this)
                                    );
                                } else {
                                    if (this.thankyouPage !== "1") {
                                        this.$billingEmail.after(
                                            resp.data.html
                                        );
                                    } else {
                                        $("#verify_div").html(resp.data.html);
                                    }
                                }
                                trustVerify.configs = {
                                    embedKey: TSCheckoutConfig.embed_key,
                                    signature: resp.data.ts_user_signature,
                                    baseUrl: TSCheckoutConfig.base_url,
                                    type: "modal_link",
                                    verifyDivId: "trustVerify",
                                    userId: resp.data.ts_embed_user_id,
                                    moduleType: "wordpress",
                                };

                                trustVerify.load();

                                return;
                            }
                            if (this.thankyouPage === "1") {
                                pre1 =
                                    '<div class="col-6" style="margin-top: 20px"> ' +
                                    '<div class="row" style="margin-top: 20px"> ';
                            }

                            var url = resp.data.link;
                            img = TSCheckoutConfig.btn_img;
                            jQuery(".ts-loader").remove();
                            if (is_verified === 1) {
                                jQuery("#ts-verify-link").remove();
                                let $checkBtn;
                                img = "'" + img + "'";
                                $checkBtn = $(
                                    '<span class="ts_emb_verify">' +
                                        '<div class="row" style="margin-top: 20px"> ' +
                                        pre1 +
                                        '<a id="ts-verify-link" class="ts-verify-link col-8" style="background-image: url(' +
                                        img +
                                        ');margin: auto" target="_blank" href="' +
                                        url +
                                        '"></a>' +
                                        '<i class="fa fa-hourglass col-1" style="margin: auto;color:orange"></i>' +
                                        '<button id="ts-verify-check-button" class="ts-check-verify btn btn-small col-3" type="button">' +
                                        '<i class="fa fa-refresh"></i></button>' +
                                        pre2 +
                                        "</div></span>"
                                );
                                if (this.thankyouPage !== "1") {
                                    this.$billingEmail.after($checkBtn);
                                } else {
                                    $("#verify_div").html($checkBtn);
                                }
                                $("#ts-verify-check-button").on(
                                    "click",
                                    this.checkVerify.bind(this)
                                );
                            } else {
                                jQuery("#ts-verify-link").remove();
                                var $btn = $(
                                    '<a id="ts-verify-link" class="ts-verify-link" target="_blank" href="' +
                                        url +
                                        '"></a>'
                                );
                                $btn = $btn.css({
                                    "background-image": "url(" + img + ")",
                                });
                                if (this.thankyouPage !== "1") {
                                    this.$billingEmail.after($btn);
                                } else {
                                    $("#verify_div").html($btn);
                                }
                            }
                        } else if (resp.data.type == "user_already_verified") {
                            $(".ts_emb_verify").hide();
                            let $btn =
                                "<span class='ts_verification_successful' style='color:green;" +
                                (jQuery(
                                    ".wc-block-components-address-form__email"
                                ).length !== 0
                                    ? "display:block"
                                    : "") +
                                "'>Verification Successful <i class='fa fa-check' style='color:green'></i></span>";
                            if (this.thankyouPage !== "1") {
                                this.$billingEmail.after($btn);
                            } else {
                                $("#verify_div").html($btn);
                            }
                        }
                    }.bind(this)
                ).done(function (resp) {
                    $(".ts-loader").remove();
                });
            },
            isEmailValid: function (email) {
                var pattern = new RegExp(
                    /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[0-9a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i
                );
                // eslint-disable-line max-len

                return pattern.test(email);
            },
            checkVerify: function (e) {
                $.post(
                    this.config.ajaxUrl,
                    {
                        action: "ts_check_user_verification",
                        nonce: this.config.nonce,
                    },
                    function (resp) {
                        if (!resp.success) {
                            return;
                        }

                        if (resp.data.is_verified == "1") {
                            $(".ts_emb_verify").hide();
                            let $btn =
                                "<span style='color:green'>Verification Successful <i class='fa fa-check' style='color:green'></i></span>";
                            if (this.thankyouPage !== "1") {
                                this.$billingEmail.after($btn);
                            } else {
                                $("#verify_div").html($btn);
                            }
                        }
                    }.bind(this)
                );
            },
            hideIt: function (e) {
                $(".ts_emb_verify").hide();
                $(".ts-verify-link").hide();
            },
        };
    });
    var fInterval = 0;
    var bIsVerified = 0;
    var funcAreAllItemsLoaded = function () {
        /*if(jQuery('input[name=payment_method]').length!==0){
            //return ;
        }*/
        if (
            jQuery("#email").length !== 0 ||
            jQuery("#billing_email").length !== 0
        ) {
            if (TSCheckoutConfig.thank_you_page !== "1") {
                window.TSVerification.init();
            }

            jQuery(".wc-block-components-checkout-place-order-button").click(
                function (event) {
                    if (bIsVerified) {
                        return true;
                    }
                    event.preventDefault();
                    var email = "";
                    if (jQuery("#email").length !== 0) {
                        email = jQuery("#email").val();
                    }
                    if (jQuery("#billing_email").length !== 0) {
                        email = jQuery("#billing_email").val();
                    }

                    if (!email || !isEmailValid(email)) {
                        return;
                    }

                    var payment_method = "unknown";
                    if (
                        jQuery(
                            "input[name=radio-control-wc-payment-method-options]:checked"
                        ).length !== 0
                    ) {
                        payment_method = jQuery(
                            "input[name=radio-control-wc-payment-method-options]:checked"
                        ).val();
                    }

                    if (payment_method == "unknown") {
                        payment_method = jQuery(
                            "input[name=payment_method]:checked"
                        ).val();
                    }
                    if (typeof payment_method === "undefined") {
                        payment_method = "unknown";
                    }
                    jQuery.ajax({
                        type: "POST",
                        url: TSCheckoutConfig.ajax_url,
                        data: {
                            action: "ts_verifyVerification",
                            email: email,
                            payment_method: payment_method,
                        },
                        success: function (data) {
                            if (data.message != "") {
                                jQuery(
                                    ".ts-err-verification-required"
                                ).remove();
                                jQuery(
                                    ".wp-block-woocommerce-checkout-actions-block"
                                ).prepend(
                                    '<div class="woocommerce-error ts-err-verification-required" role="alert">' +
                                        data.message +
                                        "</div>"
                                );
                            } else {
                                bIsVerified = 1;
                                jQuery(
                                    ".wc-block-components-checkout-place-order-button"
                                ).click();
                            }
                        },
                        error: function (data) {},
                    });

                    return false;
                }
            );
        }
        if (
            jQuery("#email").length !== 0 ||
            jQuery("#billing_email").length !== 0
        ) {
            clearInterval(fInterval);
        }
    };
    fInterval = window.setInterval(funcAreAllItemsLoaded, 1000);
})(jQuery);
