# Local Lead Router

Local Lead Router is a lightweight WordPress plugin MVP for local service businesses that need to capture leads, route them to the right email inbox, and manage basic follow-up inside WordPress.

## MVP scope

- Shortcode form: `[lead_router_form]`
- Service-based email routing
- Lead storage in a custom WordPress table
- Lead inbox with simple statuses
- UTM source, medium, and campaign capture
- Honeypot spam protection
- Optional consent checkbox

## Target niche

The first niche is local service businesses and small agencies: plumbers, repair services, contractors, clinics, studios, and service teams that need different request types to reach different inboxes.

This niche is intentionally simpler than a full CRM and easier to validate than a generic form builder.

## Install locally

1. Copy the `local-lead-router` folder into `wp-content/plugins/`.
2. Activate the plugin in WordPress.
3. Go to **Lead Router > Settings**.
4. Configure service routes and recipient emails.
5. Add `[lead_router_form]` to a page.

## Notes

Email delivery depends on `wp_mail()` and the hosting provider. For production sites, pair this with a reliable SMTP plugin.
