"use strict";

function obpRecaptchaV2() {
  const recaptcha = document.getElementsByClassName('obp-recaptcha-wrapper');
  for (let i = 0; i < recaptcha.length; i++) {
    grecaptcha.render(recaptcha.item(i), {
      sitekey: obp_recaptcha.site_key
    });
  }
}
function obpRecaptchaV3() {
  grecaptcha.execute(obp_recaptcha.site_key, {
    action: 'validate_recaptchav3'
  }).then(function (token) {
    document.querySelectorAll('.obp-recaptcha-response').forEach(function (elem) {
      elem.value = token;
    });
  });
}