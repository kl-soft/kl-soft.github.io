<?php
	
	function is_ajax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) ? (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') === 0) : false;
	}

	// redirect to index page all non-ajax requests
	if (!is_ajax()) {
		header('Location: /');
		exit;
	}

	// init form values and validation rules
	$form['email'] = array(
		'value' => isset($_POST['email']) ? stripslashes($_POST['email']) : '',
		'regexp' => '/.+@.+\..+/i'
	);
	$form['name'] = array(
		'value' => isset($_POST['name']) ? stripslashes($_POST['name']) : '',
		'regexp' => '/^.{2,100}$/' // any char except new line, length from 2 to 100
	);
	$form['body'] = array(
		'value' => isset($_POST['body']) ? stripslashes($_POST['body']) : '',
		'regexp' => '/^.{2,1024}$/s' // any char including new line, length from 2 to 1024
	);
	$form['js_executed'] = array(
		'value' => isset($_POST['js_executed']) ? $_POST['js_executed'] : '',
		'regexp' => '/^yes$/'
	);

	// validate fields
	$form_valid = true;
	foreach ($form as $field) {
		$form_valid = $form_valid && (preg_match($field['regexp'], $field['value']) === 1);
	}

	// process form submit
	if ($form_valid) {
		// compose email
		$to = 'info@kl-soft.com';
		$from = $form['name']['value'] . ' <' . $form['email']['value'] . '>';
		$subject = '[kl-soft.com: site feedback]';
		$body  = $form['body']['value']; 
		$body .= "\n\n-----------------------------------------\n";
		$body .= 'Remote IP: ' . $_SERVER['REMOTE_ADDR'] . "\n";
		$body .= 'Remote Host: ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "\n";
		$body .= 'User Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\n";
		$body .= 'Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$header  = 'From: ' . $from . "\n";
		$header .= 'Reply-To: ' . $from . "\n";
		$header .= 'X-Mailer: PHP mailer';

		if (mail($to, $subject, $body, $header)) {
			$result['result'] = 'ok';
			$result['message'] = '<strong>Thank you! Your feedback is important to us.</strong><br>Please keep in mind we speak English only. If our reply takes too long please check your "Spam" folder.';
		} else {
			$result['result'] = 'fail';
			$result['message'] = '<strong>Error:</strong> Can\'t send your message. Please, try again.';
		}
	} else {
		$result['result'] = 'fail';
		$result['message'] = '<strong>Error:</strong> make sure all fields are correctly filled!';
	}

	header('Content-Type: application/json'); 
	echo json_encode($result);

