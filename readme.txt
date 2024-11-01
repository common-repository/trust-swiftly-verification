=== Top Identity Verifications for WooCommerce | Trust Swiftly ===
Contributors: trustswiftly
Tags: id verification, fraud prevention, verify id, woocommerce, age verify
Requires at least: 6.5
Tested up to: 6.6.1
Requires PHP: 8.1
Stable tag: 1.1.11
License: GPLv2 or later

Flexible, secure, and accurate identity verifications for WooCommerce stores

== Description ==

Trust Swiftly provides flexible and accurate identity verifications with over 15 different verification methods. Trust Swiftly is easily customizable through our dashboard and configurable with Woocommerce to optimize your customer's experience with frictionless verifications.

Trust Swiftly helps businesses adaptively fight fraud by applying the right friction when needed. From SMS verification to ID, Banking, Voice, Signatures and Credit Cards, you can be sure all checks are covered.

* Verify age for restricted products (Alcohol, cannabis and vaping)
* Prevent fraud from risky transactions (Chargebacks and high value goods)
* Verify all country IDs, Driver License, Passports, State IDs, and more
* Adhere to KYC and AML regulations

[youtube https://www.youtube.com/watch?v=9aUsSZgLXOo]

Trust Swiftly supports all types of identity documents from hundreds of different countries. Supported documents include:

* Driver’s Licenses
* Passports
* Insurance Cards
* Concealed Carry Licenses
* State IDs
* Country IDs
* National ID Cards

Start using the best and top identity verification plugin for WooCommerce. 

== Installation ==

**Prerequisite:** A Trust Swiftly account. Sign up for free [https://app.trustswiftly.com/create](https://app.trustswiftly.com/create) 

= Setup in Trust Swiftly and WP Plugin = 

To configure the integration with Trust Swiftly, follow the steps below.

1. Log in to the Trust Swiftly dashboard. Your **base url** is your branded site name you login i.e. `https://EXAMPLE.trustswiftly.com`
2. **Create a Template** in the Trust Swiftly dashboard to configure the user verification steps. Read the documentation on managing templates for more information. [How to create templates?](https://support.trustswiftly.com/how-to-manage-templates.html) 
3. Navigate to the **Developer > API** page to create an API key. i.e. `https://EXAMPLE.trustswiftly.com/settings/developer`
4. (Optional) Navigate to the **Developer > Webhooks** page to create an IPN which notifies your store about completed verifications incase of redirect problems. Click add Webhook at enter the IPN listed on your TS settings page. i.e. `https://example.com/wp-json/ts/v1/ipn` Afterwards make sure to copy the secret from the button to input later.
5. Update in your Trust Swiftly dashboard settings the **Completion Redirect URL**, Go to `https://EXAMPLE.trustswiftly.com/settings` copy and paste the url given on the Wordpress TS settings page and update. i.e. `https://example.com/wp-json/ts/v1/return/?order_id={order_id}`
6. You should now have the **API Key, Secret, and Embed Key** then update in the Wordpress settings of Trust Swiftly. The webhoook secret should also be added if that feature is enabled. 
7. Click Save to test the API connection. Once it is confirmed you can then select the remaining settings like the Verification template or trigger location of the verification (Before or after checkout). 

== Frequently Asked Questions ==

= How does it work?

Trust Swiftly uses AI to automatically detect and verify identities. Driver license, passports, and more can be checked for age and fraud related checks. You can learn how the customer receives the email, link, or SMS to complete the verification. [https://trustswiftly.com/features/](https://trustswiftly.com/features/)

= What countries and types of identity documents does your system support?

Trust Swiftly supports all types of ID documents from hundreds of different countries. Supported documents include:

* Driver’s Licenses
* Passports
* Insurance Cards
* Concealed Carry Licenses
* State IDs
* Country IDs
* National ID Cards

If you find an ID that doesn't work let us know. We’d be happy to help by adding it to our system.

= How much does this cost?

Depending on the verification we offer different pricing. As low as $0.01 per verify. No montly commitments with pay as you go pricing. You can learn more here [https://trustswiftly.com/#pricing](https://trustswiftly.com/#pricing)

= What is Trust Swiftly? =

Trust Swiftly is a cloud-based identity verification software designed to help businesses verify their customers through over 15 different security capabilities. Adding this plugin, gives anyone the ability to step up identity proofing on Wordpress. The plugin will allow you to verify IDs, selfies, credit cards, addresses, SSNs, phone numbers, signatures and much more. Use Trust Swiftly to verify your customers to prevent chargebacks or when they purchase age restricted products.

See [https://trustswiftly.com/features/](https://trustswiftly.com/features/) for more info.

= Does this work with any other WooCommerce plugins?

Yes Trust Swiftly works with the top rated Anti-Fraud plugin. Combining our identity verifications with Anti-Fraud AI fraud detection will secure your site. You can learn more here [https://woo.com/document/woocommerce-anti-fraud/](https://woo.com/document/woocommerce-anti-fraud/)

= Need help getting started or changes?

We are always looking to improve our plugin and any feedback is appreciated. Contact us at support@trustswiftly.com for support or feedback. Our support site also has useful tips on preventing fraud [https://support.trustswiftly.com/](https://support.trustswiftly.com/)

== Changelog ==

= 1.0.0 =
Initial release.

= 1.0.1 =
* Permission fix
* Updated readme

= 1.0.2 =
* Updated readme

= 1.0.3 =
* Updated css assets for buttons 

= 1.0.4 =
* Version fix

= 1.0.5 =
* Fix icons loading

= 1.0.7 =
* Fix bugs

= 1.0.8 =
* Added feature to limit payment methods that require verification

= 1.1.3 =
* Fixed API warning.

= 1.1.4 =
* Add email notify option

= 1.1.6 =
* Added support for 6.4 and identity verification option enhancements

= 1.1.10 =
* Added support for WooCommerce Block checkout

= 1.1.11 =
* Fix block checkout bug
