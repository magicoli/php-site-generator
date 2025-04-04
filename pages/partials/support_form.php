<?php
// This file is auto-generated. Do not edit manually.
$github_user = "{{github_user}}";
$repo = "{{github_repo}}";
$github_token = "{{github_token}}";

// This page will be included in the main template, do not exit

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     die("Invalid request method.");
// }

$issue_title = trim($_POST['issue_title'] ?? '');
$issue_description = trim($_POST['issue_description'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');
$contact_name = trim($_POST['contact_name'] ?? '');

if (empty($issue_title) || empty($issue_description)) {
    echo '<div class="alert alert-warning" role="alert">';
    echo "This page is still under development, form will not be processed.";
    echo '</div>';
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
            <button type="submit" class="btn btn-primary">Submit Issue</button>
        </form>',
        htmlspecialchars($contact_name),
        htmlspecialchars($contact_email),
        htmlspecialchars($issue_title),
        htmlspecialchars($issue_description),
    );
    echo $form;
} else {
    // Will process the form here and display the result instead of the form
    // Not for now, we'll see that later.
    printf(
        '<div class="alert alert-success" role="alert">
            <strong>Success!</strong> Your issue has been submitted.
            <p>(Actually, it was not processed, we are only simulating the success message)</p>
            <ul>
                <li>Contact: <a href="mailto:%s">%s</a></li>
                <li>Title: %s</li>
                <li>Description: %s</li>
            </ul></div>',
        htmlspecialchars($contact_email),
        htmlspecialchars(empty($contact_name) ? $contact_email : $contact_name),
        htmlspecialchars($issue_title),
        htmlspecialchars($issue_description),
    );
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
