
/**
 * Layout Components...
 */
require('./spark-components/navbar/navbar');
require('./spark-components/notifications/notifications');

/**
 * Authentication Components...
 */
require('./spark-components/auth/register-stripe');
require('./spark-components/auth/register-braintree');

/**
 * Settings Component...
 */
require('./spark-components/settings/settings');

/**
 * Profile Settings Components...
 */
require('./spark-components/settings/profile');
require('./spark-components/settings/profile/update-profile-photo');
require('./spark-components/settings/profile/update-contact-information');

/**
 * Teams Settings Components...
 */
require('./spark-components/settings/teams');
require('./spark-components/settings/teams/create-team');
require('./spark-components/settings/teams/pending-invitations');
require('./spark-components/settings/teams/current-teams');
require('./spark-components/settings/teams/team-settings');
require('./spark-components/settings/teams/team-profile');
require('./spark-components/settings/teams/update-team-photo');
require('./spark-components/settings/teams/update-team-name');
require('./spark-components/settings/teams/team-membership');
require('./spark-components/settings/teams/send-invitation');
require('./spark-components/settings/teams/mailed-invitations');
require('./spark-components/settings/teams/team-members');

/**
 * Security Settings Components...
 */
require('./spark-components/settings/security');
require('./spark-components/settings/security/update-password');
require('./spark-components/settings/security/enable-two-factor-auth');
require('./spark-components/settings/security/disable-two-factor-auth');

/**
 * API Settings Components...
 */
require('./spark-components/settings/api');
require('./spark-components/settings/api/create-token');
require('./spark-components/settings/api/tokens');

/**
 * Subscription Settings Components...
 */
require('./spark-components/settings/subscription');
require('./spark-components/settings/subscription/subscribe-stripe');
require('./spark-components/settings/subscription/subscribe-braintree');
require('./spark-components/settings/subscription/update-subscription');
require('./spark-components/settings/subscription/resume-subscription');
require('./spark-components/settings/subscription/cancel-subscription');

/**
 * Payment Method Components...
 */
require('./spark-components/settings/payment-method-stripe');
require('./spark-components/settings/payment-method-braintree');
require('./spark-components/settings/payment-method/update-payment-method-stripe');
require('./spark-components/settings/payment-method/update-payment-method-braintree');
require('./spark-components/settings/payment-method/redeem-coupon');

/**
 * Billing History Components...
 */
require('./spark-components/settings/invoices');
require('./spark-components/settings/invoices/update-extra-billing-information');
require('./spark-components/settings/invoices/invoice-list');

/**
 * Kiosk Components...
 */
require('./spark-components/kiosk/kiosk');
require('./spark-components/kiosk/announcements');
require('./spark-components/kiosk/metrics');
require('./spark-components/kiosk/users');
require('./spark-components/kiosk/profile');
require('./spark-components/kiosk/add-discount');
