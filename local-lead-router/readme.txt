=== Local Lead Router ===
Contributors: localleadrouter
Tags: leads, lead routing, contact form, local business, crm
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Capture local service leads, route them to the right inbox, and manage follow-up inside WordPress.

== Description ==

Local Lead Router is a lightweight lead routing plugin for local service businesses, small agencies, and teams that need a simple way to send website requests to the right person.

The MVP includes:

* A shortcode lead form: `[lead_router_form]`
* Service-based email routing
* Lead storage inside WordPress
* A lead inbox with status tracking
* CSV export
* Email delivery logs
* Diagnostics screen
* UTM source, medium, and campaign capture
* Honeypot spam protection
* Basic rate limiting
* Consent checkbox option

No external service is required.

== Installation ==

1. Upload the `local-lead-router` folder to `/wp-content/plugins/`.
2. Activate Local Lead Router from the Plugins screen.
3. Go to Lead Router > Settings.
4. Configure service options and recipient emails.
5. Add `[lead_router_form]` to any page.

== Frequently Asked Questions ==

= Does this replace a full CRM? =

No. It is intentionally focused on lead capture, routing, and basic follow-up status.

= Does this use a paid API? =

No. Emails are sent through WordPress using `wp_mail()`.

= Why did my email not arrive? =

`wp_mail()` depends on the hosting email configuration. If delivery is unreliable, use a trusted SMTP plugin.

== Changelog ==

= 0.2.0 =
* Added CSV export.
* Added email delivery logs.
* Added diagnostics screen.
* Added rate limiting and improved form error handling.
* Added dynamic route rows in settings.

= 0.1.0 =
* Initial MVP release.
