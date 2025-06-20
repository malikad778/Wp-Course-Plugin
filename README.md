# WP Course Plugin

## Description

The WP Course Plugin is a comprehensive solution for creating and managing online courses directly within your WordPress website. It provides robust features for course creation,  payment processing (via Stripe and PayPal), and content delivery, enabling you to build a powerful e-learning platform.

## Features

*   **Course Management:** Easily create, edit, and organize courses with multiple lessons and modules (you will need to create a custom post type with 'course' as type).
*   **Stripe Integration:** Securely process payments for courses using Stripe, supporting various payment methods.
*   **Subscription Plans:** Offer different subscription tiers for access to courses or course bundles.
*   **Admin Dashboard:** A dedicated admin area for managing courses, orders, and plugin settings.
*   **Frontend Templates:** Includes pre-built templates for displaying course listings, checkout pages, and thank you pages.
*   **Login/Registration Popups:** Integrated popups for seamless user login and registration.

## Installation

1.  **Download the Plugin:** Download the `wp-course_2.zip` file.
2.  **Upload via WordPress:**
    *   Navigate to `Plugins > Add New` in your WordPress admin dashboard.
    *   Click on the `Upload Plugin` button.
    *   Choose the `wp-course_2.zip` file and click `Install Now`.
3.  **Activate the Plugin:** After installation, click `Activate Plugin`.
4. ** Add the code from function.php to your theme function.php file
   
 ## Configuration

To fully configure the WP Course Plugin, you will need to set up a custom course type post by installing the ACF plugin and adding a group field and course ( Code included in function.php), your Stripe API keys, and define your course plans.

1.  **Stripe API Keys:**
    *   Log in to your Stripe Dashboard.
    *   Navigate to `Developers > API keys`.
    *   Locate your **Publishable key** and **Secret key**.
    *   In your WordPress admin, go to `WP Course > Settings`.
    *   Enter your Stripe Publishable Key and Secret Key into the respective fields.
    *   Save Changes.
2.  **Create Course Plans:**
    *   Go to `WP Course > Plans` in your WordPress admin.
    *   Click `Add New Plan`.
    *   Define the plan name, description, price, and associated courses.
    *   Save your plan.
 

## Usage

Once configured, you can start offering courses:

1.  **Create Courses:** Go to `Courses > Add Courses` to add and manage your course content.
3.  **Display on Frontend:** Use the shortcode [courses_displays] to display your course offerings on your website pages.
4.  **Manage Orders:** Monitor and manage student enrollments and payments via `WP Course Subscriptions > Orders`.



