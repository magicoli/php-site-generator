<?php
// This file is auto-generated. Do not edit manually.
$github_user = "{{github_user}}";
$repo = "{{github_repo}}";
$github_token = "{{github_token}}";
$site_name = "{{title}}";
$support_email = "{{support_email}}";
$sender_email = "{{sender_email}}";

// This page will be included in the main template, do not exit

// Check if support email is configured
if ( empty($support_email) ) {
    echo '<div class="alert alert-secondary" role="alert">
        Support form is not enabled. 
    </div>';
    // Exit early - don't process or display the form
    return;
}

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     die("Invalid request method.");
// }

$issue_title = trim($_POST['issue_title'] ?? '');
$issue_description = trim($_POST['issue_description'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');
$contact_name = trim($_POST['contact_name'] ?? '');
$success = false;
$errors = []; // Initialize errors array

# Process the form if submitted
if( isset($_POST['submit']) && $_POST['submit'] == 'support_request' ) {
    // Validate required fields
    if (empty($issue_title)) {
        $errors['issue_title'] = "Issue title is required";
    }
    if (empty($issue_description)) {
        $errors['issue_description'] = "Issue description is required";
    }
    if (empty($contact_email)) {
        $errors['contact_email'] = "Contact email is required";
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors['contact_email'] = "Invalid email format";
    }
    
    // Process if no validation errors
    if (empty($errors)) {
        // Prepare email content
        
        // Send confirmation email to requester
        $confirmation_subject = "Your support request was received - {$issue_title}";
        $confirmation_message = "
            <html>
            <head><title>Support Request Confirmation</title></head>
            <body>
                <h2>Thank you for your support request</h2>
                <p>We have received your support request with the following details:</p>
                <p><strong>Title:</strong> {$issue_title}</p>
                <p><strong>Description:</strong> {$issue_description}</p>
                <p>We'll review your request and get back to you as soon as possible.</p>
                <p>Best regards,<br>The {$site_name} Team</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$site_name} <{$sender_email}>" . "\r\n";
        $headers .= "Reply-To: {$support_email}" . "\r\n";

        // Send notification to admin with reply-to set to contact email
        $notification_subject = "[Support Request] {$issue_title}";
        $notification_message = "
        <html>
        <head><title>New Support Request</title></head>
        <body>
        <h2>New Support Request Received</h2>
        <p><strong>From:</strong> " . (!empty($contact_name) ? "{$contact_name} ({$contact_email})" : $contact_email) . "</p>
        <p><strong>Title:</strong> {$issue_title}</p>
        <p><strong>Description:</strong> {$issue_description}</p>
        </body>
        </html>
        ";
        
        $notification_headers = "MIME-Version: 1.0" . "\r\n";
        $notification_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $notification_headers .= "From: {$site_name} <{$sender_email}>" . "\r\n";
        $notification_headers .= "Reply-To: {$contact_email}" . "\r\n";
        
        $notification_sent = mail($support_email, $notification_subject, $notification_message, $notification_headers);
        if( $notification_sent ) {
            $success = true;
            $confirmation_sent = mail($contact_email, $confirmation_subject, $confirmation_message, $headers);
            if( !$confirmation_sent ) {
                $errors['contact_email'] = sprintf(
                    'Confirmation mail to %s failed, but support has been notified.',
                    htmlspecialchars($contact_email),
                );
            }
        } else {
            $errors['admin_email'] = sprintf(
                'Mail could not be sent to support, your request could not be processed.',
            );
        }
    }
}

if (!empty($errors)) {
    echo '<div class="alert alert-danger" role="alert">';
    echo '<strong>Errors:</strong><ul class="mb-0">';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul></div>';
}

# Display form if post empty or processing failed with error, otherwise only display success message
if ($success) {
    printf(
        '<div class="alert alert-success" role="alert">
            <strong>Success!</strong> Your issue has been submitted.
            <p>(Actually, it was not processed, we are only simulating the success message)</p>
            <ul class="mb-0">
                <li>Contact: <a href="mailto:%s">%s</a></li>
                <li>Title: %s</li>
                <li>Description: %s</li>
            </ul></div>',
        htmlspecialchars($contact_email),
        htmlspecialchars(empty($contact_name) ? $contact_email : $contact_name),
        htmlspecialchars($issue_title),
        htmlspecialchars($issue_description),
    );
} else {
    // Display form with available field values if any
    $form = sprintf(
        '<form method="post">
            <div class="mb-3">
                <label for="contact_name" class="form-label">Contact Name</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name" value="%s">
            </div>
            <div class="mb-3">
                <label for="contact_email" class="form-label">Contact Email (required)</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email" value="%s" required>
            </div>
            <div class="mb-3">
                <label for="issue_title" class="form-label">Issue Title (required)</label>
                <input type="text" class="form-control" id="issue_title" name="issue_title" value="%s" required>
            </div>
            <div class="mb-3">
                <label for="issue_description" class="form-label">Issue Description (required)</label>
                <textarea class="form-control" id="issue_description" name="issue_description" rows="5" required>%s</textarea>
            </div>
            <button type="submit" name="submit" value="support_request" class="btn btn-primary">Submit Issue</button>
        </form>',
        htmlspecialchars($contact_name),
        htmlspecialchars($contact_email),
        htmlspecialchars($issue_title),
        htmlspecialchars($issue_description),
    );
    echo $form;
}

## Following is the Old proposal, likely to be removed, ignore it

// if (!empty($contact_email)) {
//     $issue_description .= "\n\nContact Email: " . $contact_email;
// }

// $data = array(
//     "title" => $issue_title,
//     "body"  => $issue_description
// );

// $url = "https://api.github.com/repos/{$github_user}/{$repo}/issues";
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_USERAGENT, "PHP Site Generator");
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     "Content-Type: application/json",
//     "Authorization: token " . $github_token
// ));
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
// $response = curl_exec($ch);
// $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// if (curl_errno($ch)) {
//     die("cURL error: " . curl_error($ch));
// }
// curl_close($ch);

// if ($http_code == 201) {
//     $issue = json_decode($response, true);
//     $issue_url = htmlspecialchars($issue["html_url"] ?? "");
//     echo "Issue submitted successfully. <a href='{$issue_url}'>View Issue on GitHub</a>";
// } else {
//     echo "Failed to submit issue. HTTP response code: {$http_code}.<br>Response: " . htmlspecialchars($response);
// }
