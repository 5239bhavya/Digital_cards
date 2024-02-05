<?php

// Sanitize input data
foreach ($_POST as $key => $value) {
    $_POST[$key] = htmlentities($value);
}

$errors = 0;
$name_error = '';
$email_error = '';
$viol_error = '';

if (!isset($_POST['Submit'])) {
    // Display form
    $page = new HtmlTemplate("templates/" . $config['tpl_name'] . "/report.tpl");
    $page->SetParameter('OVERALL_HEADER', create_header($lang['REPORTVIO']));
    $page->SetParameter('USERNAME2', '');
    $page->SetParameter('DETAILS', '');
    $page->SetParameter('EMAIL_ERROR', '');
    $page->SetParameter('NAME_ERROR', '');
    $page->SetParameter('VIOL_ERROR', '');

    if (isset($_SESSION['user']['username'])) {
        $ses_userdata = get_user_data($_SESSION['user']['username']);
        $page->SetParameter('USERNAME', $_SESSION['user']['username']);
        $page->SetParameter('NAME', $ses_userdata['name']);
        $page->SetParameter('EMAIL', $ses_userdata['email']);
    } else {
        $page->SetParameter('USERNAME', '');
        $page->SetParameter('NAME', '');
        $page->SetParameter('EMAIL', '');
    }

    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        if ((strpos($referer, $link['POST-DETAIL']) !== false)) {
            $page->SetParameter('REDIRECT_URL', $_SERVER['HTTP_REFERER']);
        } else {
            $page->SetParameter('REDIRECT_URL', '');
        }
    } else {
        $page->SetParameter('REDIRECT_URL', '');
    }

    $page->SetParameter('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
} else {
    // Form submitted, validate and process data

    // Regular expression for email validation
    $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

    // Validate email
    if (trim($_POST['email']) == '') {
        $errors++;
        $email_error = $lang['ENTEREMAIL'];
    } elseif (!preg_match($regex, $_POST['email'])) {
        $errors++;
        $email_error = $lang['EMAILINV'];
    }

    // Validate name
    if (trim($_POST['name']) == '') {
        $errors++;
        $name_error = $lang['ENTERNAME'];
    }

    // Validate details/violation
    if (trim($_POST['details']) == '') {
        $errors++;
        $viol_error = $lang['ENTERVIOL'];
    }

    // Process form if no errors
    if ($errors == 0) {
        /*SEND CONTACT EMAIL*/
        if (email_template("report")) {
            message($lang['THANKS'], $lang['REPORT_THANKS']);
        } else {
            // Failed to send email
            error_log("Failed to send contact email");
            message($lang['ERROR'], $lang['EMAIL_SEND_ERROR']);
        }
    } else {
        // Display form with errors
        $page = new HtmlTemplate("templates/" . $config['tpl_name'] . "/report.tpl");
        $page->SetParameter('OVERALL_HEADER', create_header($lang['REPORTVIO']));

        $page->SetParameter('USERNAME', $_POST['username']);
        $page->SetParameter('USERNAME2', $_POST['username2']);
        $page->SetParameter('NAME', $_POST['name']);
        $page->SetParameter('EMAIL', $_POST['email']);
        $page->SetParameter('DETAILS', $_POST['details']);

        $page->SetParameter('EMAIL_ERROR', $email_error);
        $page->SetParameter('NAME_ERROR', $name_error);
        $page->SetParameter('VIOL_ERROR', $viol_error);

        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            if ((strpos($referer, $link['AD-DETAIL']) !== false)) {
                $page->SetParameter('REDIRECT_URL', $_SERVER['HTTP_REFERER']);
            } else {
                $page->SetParameter('REDIRECT_URL', '');
            }
        } else {
            $page->SetParameter('REDIRECT_URL', '');
        }

        $page->SetParameter('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
    }
}
?>
