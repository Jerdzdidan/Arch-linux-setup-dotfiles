# Email Sending in Laravel — Reference Guide

A reference for how the AU-AIS announcement system sends emails, and the tools/concepts behind it.

---

## Tools & Libraries Used

| Tool | What It Does | Docs |
|------|-------------|------|
| **Laravel Mail** | Built-in email framework. No extra packages needed. | [laravel.com/docs/mail](https://laravel.com/docs/11.x/mail) |
| **Laravel Queues** | Sends emails in the background so the user doesn't wait. | [laravel.com/docs/queues](https://laravel.com/docs/11.x/queues) |
| **Mailables** | PHP class that defines an email (subject, view, data). | Part of Laravel Mail |
| **Jobs** | PHP class that runs a task in the background via the queue. | Part of Laravel Queues |
| **Gmail SMTP** | The mail server that actually delivers the email. | [Google App Passwords](https://myaccount.google.com/apppasswords) |
| **Quill.js** | Rich text editor for composing HTML email bodies. | [quilljs.com](https://quilljs.com/) |

---

## How It All Fits Together

```
User clicks "Send"
       │
       ▼
Controller validates input
       │
       ▼
Creates Announcement record in DB
       │
       ▼
Dispatches SendAnnouncementEmail Job ──► Job goes into `jobs` table (queue)
       │
       ▼
Response sent back instantly ("Queued!")
       
Meanwhile, in a separate terminal...

php artisan queue:work
       │
       ▼
Picks up the job from the `jobs` table
       │
       ▼
Queries recipients using buildRecipientsQuery()
       │
       ▼
Loops through each recipient
       │
       ▼
Mail::to($email)->send(new AnnouncementMail($announcement))
       │
       ▼
Laravel connects to Gmail SMTP → Email delivered
```

---

## Key Files & What They Do

### 1. Mailable — `app/Mail/AnnouncementMail.php`
Defines the email: what subject to use, which Blade template to render, and what data to pass.

```php
// Creating a Mailable
php artisan make:mail AnnouncementMail
```

Key concepts:
- `envelope()` — sets the subject line
- `content()` — points to the Blade view for the email body
- The constructor receives data (e.g., an `Announcement` model) that becomes available in the view

### 2. Job — `app/Jobs/SendAnnouncementEmail.php`
A queued job that does the actual sending in the background.

```php
// Creating a Job
php artisan make:job SendAnnouncementEmail
```

Key concepts:
- `implements ShouldQueue` — this is what makes it run in the background
- `handle()` — the method that runs when the queue worker picks it up
- `dispatch()` — how you push the job onto the queue from the controller

### 3. Email Template — `resources/views/emails/announcement.blade.php`
A standard Blade view, but written as a full HTML email. Uses inline styles (email clients don't support external CSS).

Key concepts:
- `{!! $announcement->body !!}` — renders raw HTML (from Quill editor)
- `{{ $announcement->subject }}` — escaped output for the title
- Use `{!! !!}` for HTML content, `{{ }}` for plain text

### 4. Controller — `app/Http/Controllers/AnnouncementController.php`
Handles the form submission, creates the DB record, and dispatches the job.

```php
// The magic line that queues the email
SendAnnouncementEmail::dispatch($announcement);
```

### 5. Migration — `database/migrations/..._create_announcements_table.php`
Creates the database table to store announcement history.

```php
php artisan make:migration create_announcements_table
```

---

## .env Configuration

```env
# Queue Driver (use database so jobs are stored in DB)
QUEUE_CONNECTION=database

# Mail Configuration (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your App Name"
```

---

## Essential Artisan Commands

```bash
# Run the queue worker (processes background jobs)
php artisan queue:work

# Create the jobs table migration (one-time setup)
php artisan queue:table
php artisan migrate

# Create a new Mailable
php artisan make:mail YourMailableName

# Create a new Job
php artisan make:job YourJobName

# Clear config cache after changing .env
php artisan config:clear

# Check failed jobs
php artisan queue:failed

# Retry a failed job
php artisan queue:retry {id}
```

---

## Quick Recipe: Sending a Simple Email

If you ever want to send a quick one-off email without queues:

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\AnnouncementMail;

// Send immediately (blocks until done)
Mail::to('someone@example.com')->send(new AnnouncementMail($data));

// Send via queue (returns immediately)
Mail::to('someone@example.com')->queue(new AnnouncementMail($data));
```

---

## Gmail Limits

- **500 emails/day** for regular Gmail accounts
- **2,000 emails/day** for Google Workspace accounts
- If you need more, consider services like **Mailgun**, **SendGrid**, or **Amazon SES**

---

## Testing Without Sending Real Emails

Set this in `.env` to log emails instead of sending:

```env
MAIL_MAILER=log
```

Emails will appear in `storage/logs/laravel.log` — great for development!
