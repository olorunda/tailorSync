# TailorFit Wiki - Comprehensive Guide

## Introduction

TailorFit is a comprehensive management system designed specifically for tailor shops and clothing alteration businesses. This wiki provides detailed information on how to use the software, from installation to advanced features.

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Getting Started](#getting-started)
4. [User Roles and Permissions](#user-roles-and-permissions)
5. [Core Features](#core-features)
   - [Dashboard](#dashboard)
   - [Client Management](#client-management)
   - [Order Management](#order-management)
   - [Appointment Scheduling](#appointment-scheduling)
   - [Invoicing and Payments](#invoicing-and-payments)
   - [Measurements](#measurements)
   - [Designs](#designs)
   - [Inventory Management](#inventory-management)
   - [Messaging](#messaging)
   - [Tasks](#tasks)
   - [Expenses](#expenses)
6. [Administrative Features](#administrative-features)
   - [User Management](#user-management)
   - [Role and Permission Management](#role-and-permission-management)
   - [Parent-Child User Relationship](#parent-child-user-relationship)
   - [System Settings](#system-settings)
   - [Data Management](#data-management)
   - [Backup and Restore](#backup-and-restore)
   - [Notifications](#notifications)
   - [Logs and Monitoring](#logs-and-monitoring)
7. [API Integration](#api-integration)
8. [Troubleshooting](#troubleshooting)
9. [FAQ](#faq)
10. [Support](#support)

## Overview

TailorFit streamlines operations for tailor shops and alteration businesses with the following key features:

- **Client Management**: Store and manage client information and measurements
- **Order Management**: Create and track orders with detailed status updates
- **Appointment Scheduling**: Schedule and manage client appointments
- **Invoicing System**: Generate professional invoices and track payments
- **Expense Tracking**: Monitor business expenses
- **Inventory Management**: Track fabric and material inventory
- **Team Management**: Manage staff and assign permissions
- **Dashboard**: Get insights into your business with financial summaries and charts
- **Measurement Management**: Store and track client measurements
- **Design Management**: Manage clothing designs and patterns
- **Task Management**: Assign and track tasks for team members
- **Messaging System**: Communicate with clients and team members
- **Parent-Child User Relationship**: Allow team members to access parent user data

## Installation

### System Requirements

Before installing TailorFit, ensure your server meets the following requirements:

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Node.js and NPM
- Web server (Apache or Nginx)
- PHP extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/tailorit.git
   cd tailorit/wtailorfit
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript Dependencies**
   ```bash
   npm install
   ```

4. **Create Environment File**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Environment Variables**
   Open the `.env` file and update the database and mail settings.

6. **Create Database**
   ```sql
   CREATE DATABASE tailorit;
   ```

7. **Run Migrations and Seeders**
   ```bash
   php artisan migrate --seed
   ```

8. **Build Assets**
   ```bash
   npm run build
   ```

9. **Set Up Scheduler (Optional)**
   Add the following Cron entry to your server:
   ```
   * * * * * cd /path-to-your-project/wtailorfit && php artisan schedule:run >> /dev/null 2>&1
   ```

10. **Configure Web Server**
    Set up a virtual host for your web server (Apache or Nginx).

11. **Set Directory Permissions**
    ```bash
    chmod -R 775 storage bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    ```

12. **Access the Application**
    Open your browser and navigate to your domain.

For detailed installation instructions, refer to the [Installation Guide](installation.md).

## Getting Started

### Default Login Credentials

After installation, you can log in with the following default credentials:

- **Email**: admin@example.com
- **Password**: password

**Important**: Change these credentials immediately after your first login.

### Initial Configuration

After logging in for the first time, you should:

1. Update your business information in **Settings** > **General**
2. Configure email settings in **Settings** > **Email**
3. Set up currency settings in **Settings** > **Currency**
4. Create user accounts for your team members
5. Set up roles and permissions

## User Roles and Permissions

TailorFit uses a role-based access control system to manage user permissions. Default roles include:

- **Administrator**: Full access to all features
- **Manager**: Access to most features except system settings
- **Tailor**: Access to orders, measurements, and designs
- **Receptionist**: Access to clients, appointments, and invoices
- **Client**: Limited access to their own orders and appointments

You can create custom roles with specific permissions as needed.

## Core Features

### Dashboard

The dashboard provides an overview of your business's key metrics and recent activities:

- **Financial Summary**: View total revenue, expenses, net profit, and pending payments
- **Charts**: Visualize revenue vs expenses and orders by status
- **Recent Orders**: Quick access to your most recent orders
- **Recent Invoices**: Quick access to your most recent invoices

### Client Management

The client management module allows you to store and manage your clients' information:

- **Adding a New Client**: Navigate to **Clients** > **Add Client** and fill in the client's information
- **Editing a Client**: Find the client in the client list, click on their name, and click **Edit**
- **Deleting a Client**: Find the client in the client list, click the three dots (⋮) menu, and select **Delete**

### Order Management

The order management module allows you to create and track orders for your clients:

- **Creating a New Order**: Navigate to **Orders** > **Create Order**, select a client, and fill in the order details
- **Updating Order Status**: Find the order in the order list, click on it, click **Edit**, update the status, and save
- **Adding Items to an Order**: In the order edit view, click **Add Item** in the Items section and fill in the item details

### Appointment Scheduling

The appointment scheduling module allows you to schedule and manage appointments with your clients:

- **Creating a New Appointment**: Navigate to **Appointments** > **Schedule Appointment**, select a client, and fill in the appointment details
- **Editing an Appointment**: Find the appointment in the calendar or list view, click on it, click **Edit**, update the details, and save
- **Cancelling an Appointment**: Find the appointment in the calendar or list view, click on it, click **Cancel**, and confirm

### Invoicing and Payments

The invoicing module allows you to create and manage invoices for your clients:

- **Creating a New Invoice**: Navigate to **Invoices** > **Create Invoice**, select a client, and fill in the invoice details
- **Creating an Invoice from an Order**: In the order details view, click **Create Invoice**, review the details, and save
- **Recording a Payment**: Navigate to **Payments** > **Record Payment**, select a client and invoice, and fill in the payment details

### Measurements

The measurements module allows you to store and manage your clients' measurements:

- **Adding Measurements for a Client**: In the client details view, click the **Measurements** tab, click **Add Measurements**, select a template or create custom measurements, and save
- **Updating Measurements**: In the client's measurements tab, find the measurement set, click **Edit**, update the values, and save

### Designs

The designs module allows you to store and manage clothing designs and patterns:

- **Adding a New Design**: Navigate to **Designs** > **Add Design**, fill in the design details, upload images, and save
- **Editing a Design**: Find the design in the design list, click on it, click **Edit**, update the details, and save

### Inventory Management

The inventory management module allows you to track your fabric and material inventory:

- **Adding a New Inventory Item**: Navigate to **Inventory** > **Add Item**, fill in the item details, and save
- **Updating Inventory Levels**: Find the item in the inventory list, click on it, click **Update Stock**, enter the new quantity or the quantity to add/remove, add a note, and save

### Messaging

The messaging module allows you to communicate with your clients and team members:

- **Sending a Message to a Client**: Navigate to **Messages** > **New Message**, select a client, enter a subject and message, and send
- **Replying to a Message**: Find the message in the message list, click on it, type your reply, and click **Reply**

### Tasks

The tasks module allows you to create and assign tasks to team members:

- **Creating a New Task**: Navigate to **Tasks** > **Create Task**, fill in the task details, assign it to a team member, and save
- **Updating Task Status**: Find the task in the task list, click on it, click **Edit**, update the status, and save

### Expenses

The expenses module allows you to track your business expenses:

- **Recording a New Expense**: Navigate to **Expenses** > **Record Expense**, fill in the expense details, upload a receipt if available, and save
- **Generating an Expense Report**: Navigate to **Expenses** > **Reports**, select the report parameters, and click **Generate Report**

## Administrative Features

### User Management

As an administrator, you can manage users of the TailorFit application:

- **Creating a New User**: Navigate to **Settings** > **Users** > **Add User**, fill in the user's information, assign a role, and save
- **Editing a User**: Find the user in the user list, click on them, click **Edit**, update the information, and save
- **Deactivating a User**: Find the user in the user list, click on them, click **Deactivate**, and confirm

### Role and Permission Management

TailorFit uses a role-based access control system to manage user permissions:

- **Creating a New Role**: Navigate to **Settings** > **Roles** > **Add Role**, fill in the role details, select permissions, and save
- **Editing a Role**: Find the role in the role list, click on it, click **Edit**, update the details, and save
- **Deleting a Role**: Find the role in the role list, click on it, click **Delete**, and confirm

### Parent-Child User Relationship

TailorFit supports a parent-child relationship between users, where a child user can access data generated by their parent user:

- **Creating a Child User**: When creating a new user, select a parent user from the dropdown
- **Viewing Child Users**: In the parent user's details, click the **Child Users** tab
- **Understanding Data Access**: Child users can access data generated by their parent user, but not data generated by other child users of the same parent

### System Settings

As an administrator, you can configure various system settings:

- **General Settings**: Business name, address, phone, email, language, timezone
- **Email Settings**: SMTP server, port, username, password, encryption, from email, from name
- **Currency Settings**: Default currency, symbol, position, separators, decimal places

### Data Management

As an administrator, you can manage the data in the TailorFit application:

- **Importing Data**: Navigate to **Settings** > **Data** > **Import**, select the data type, download the template if needed, select the file, map columns if needed, and import
- **Exporting Data**: Navigate to **Settings** > **Data** > **Export**, select the data type, format, columns to include, and export
- **Managing Measurement Templates**: Navigate to **Settings** > **Templates** > **Measurements**, view existing templates, add new templates, or edit existing ones

### Backup and Restore

As an administrator, you can backup and restore the TailorFit application data:

- **Creating a Manual Backup**: Navigate to **Settings** > **Backup**, click **Create Backup**, select the data to include, and backup
- **Configuring Scheduled Backups**: Navigate to **Settings** > **Backup** > **Schedule**, enable scheduled backups, configure the schedule, select data to include, configure storage location, and save
- **Restoring from a Backup**: Navigate to **Settings** > **Backup**, find the backup to restore from, click **Restore**, and confirm

### Notifications

TailorFit includes a notification system to send notifications to users and clients:

- **Configuring Notification Types**: Navigate to **Settings** > **Notifications**, select the notification type, enable or disable it, configure channels, and save
- **Customizing Notification Templates**: Navigate to **Settings** > **Notifications** > **Templates**, select the template, update the content, and save

### Logs and Monitoring

As an administrator, you can view logs and monitor the TailorFit application:

- **Viewing System Logs**: Navigate to **Settings** > **Logs**, select the log type, use filters to narrow down the logs, and click on a log entry to view details
- **Viewing User Activity Logs**: Navigate to **Settings** > **Logs** > **User Activity**, select a user, use filters, and click on an activity to view details
- **Viewing Error Logs**: Navigate to **Settings** > **Logs** > **Errors**, use filters, and click on an error to view details

## API Integration

TailorFit provides a RESTful API that allows developers to integrate with the application programmatically:

- **Authentication**: Generate an API token in **Settings** > **API** and include it in the `Authorization` header of all requests
- **Endpoints**: The API provides endpoints for clients, orders, invoices, payments, and more
- **Webhooks**: Configure webhooks in **Settings** > **API** > **Webhooks** to receive notifications for various events

For detailed API documentation, refer to the [API Documentation](api.md).

## Troubleshooting

If you encounter issues with TailorFit, check the following common problems and solutions:

### Installation Issues
- **Composer Install Fails**: Check PHP version, clear Composer cache, update Composer
- **NPM Install Fails**: Check Node.js version, clear NPM cache, try with --legacy-peer-deps
- **Migration Fails**: Check database credentials, permissions, try with --force

### Login and Authentication Issues
- **Can't Log In**: Check credentials, account status, try password reset
- **Session Expires Too Quickly**: Check SESSION_LIFETIME in .env, server time synchronization

### Database Issues
- **Connection Errors**: Check credentials, server status, permissions
- **Slow Queries**: Check indexes, optimize application, increase resources

### Performance Issues
- **Slow Page Loading**: Optimize application, clear caches, check server resources
- **High Memory Usage**: Check for memory leaks, optimize queries, increase PHP memory limit

For more troubleshooting information, refer to the [Troubleshooting Guide](troubleshooting.md).

## FAQ

### General Questions

**Q: What is TailorFit?**
A: TailorFit is a comprehensive management system designed specifically for tailor shops and clothing alteration businesses.

**Q: What features does TailorFit offer?**
A: TailorFit offers client management, order management, appointment scheduling, invoicing, expense tracking, inventory management, team management, and more.

**Q: Is TailorFit cloud-based or self-hosted?**
A: TailorFit can be self-hosted on your own server or hosted in the cloud.

### Technical Questions

**Q: What are the system requirements for TailorFit?**
A: TailorFit requires PHP 8.1+, MySQL 5.7+, Composer, and Node.js/NPM.

**Q: Can I customize TailorFit for my specific needs?**
A: Yes, TailorFit is built on Laravel and can be customized by developers familiar with PHP and Laravel.

**Q: Does TailorFit support multiple languages?**
A: Yes, TailorFit supports multiple languages and can be translated to your preferred language.

## Support

If you need assistance with TailorFit, you can contact support through the following channels:

- **Email**: support@tailorfit.com
- **Phone**: +1-234-567-8900
- **Support Portal**: https://support.tailorfit.com

When contacting support, please provide:
1. A detailed description of the issue
2. Steps to reproduce the issue
3. Any error messages you're seeing
4. Screenshots if applicable
5. Your system information (browser, operating system, etc.)
