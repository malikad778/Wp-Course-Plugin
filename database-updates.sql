-- Database schema updates for PayPal integration
-- Run these queries to add PayPal support to existing installations

-- Add PayPal payment tracking columns to orders table
ALTER TABLE wp_course_orders 
ADD COLUMN paypal_payment_id VARCHAR(255) NULL AFTER stripe_payment_intent_id,
ADD COLUMN payment_method VARCHAR(20) DEFAULT 'stripe' AFTER paypal_payment_id;

-- Add PayPal subscription tracking columns to subscriptions table  
ALTER TABLE wp_course_subscriptions
ADD COLUMN paypal_subscription_id VARCHAR(255) NULL AFTER stripe_subscription_id,
ADD COLUMN paypal_payment_id VARCHAR(255) NULL AFTER paypal_subscription_id;

-- Add indexes for better performance
CREATE INDEX idx_orders_paypal_payment ON wp_course_orders(paypal_payment_id);
CREATE INDEX idx_orders_payment_method ON wp_course_orders(payment_method);
CREATE INDEX idx_subscriptions_paypal_subscription ON wp_course_subscriptions(paypal_subscription_id);
CREATE INDEX idx_subscriptions_paypal_payment ON wp_course_subscriptions(paypal_payment_id);

-- Update existing records to set payment_method
UPDATE wp_course_orders SET payment_method = 'stripe' WHERE stripe_payment_intent_id IS NOT NULL;
UPDATE wp_course_orders SET payment_method = 'unknown' WHERE stripe_payment_intent_id IS NULL AND paypal_payment_id IS NULL;

