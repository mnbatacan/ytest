<?php
header("Access-Control-Allow-Origin: *");
/**
 * This sample creates and manages comments by:
 *
 * 1. Getting the top-level comments for a video via "commentThreads.list" method.
 * 2. Replying to a comment thread via "comments.insert" method.
 * 3. Getting comment replies via "comments.list" method.
 * 4. Updating an existing comment via "comments.update" method.
 * 5. Sets moderation status of an existing comment via "comments.setModerationStatus" method.
 * 6. Marking a comment as spam via "comments.markAsSpam" method.
 * 7. Deleting an existing comment via "comments.delete" method.
 *
 * @author Ibrahim Ulukaya
 */

/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';
session_start();

$name=$_GET['name'];
/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = '440105667802-ch5vnauuq9opn0kltvedtru8vbohp586.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'nuzTmgbCATp0XBBeUhNXVc-4';

/* You can replace $VIDEO_ID with one of your videos' id, and text with the
 *  comment you want to be added.
 */
$VIDEO_ID = 'qw2lI2yG-ac';
$TEXT = 'Hows the weather were having?';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);

/*
 * This OAuth 2.0 access scope allows for full read/write access to the
 * authenticated user's account and requires requests to use an SSL connection.
 */
$client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

$client->RefreshToken('1/svxOHPtl9rJQMnqq9VWVYCPIlxwaXKwyEEQr0VTr0GE');

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

if ($client->getAccessToken()) {
  try {
    $videoComments = $youtube->commentThreads->listCommentThreads('snippet', array(
        'videoId' => $VIDEO_ID,
        'textFormat' => 'plainText',
    ));
    if (empty($videoComments)) {
      // $htmlBody .= "<h3>Can\'t get video comments.</h3>";
    } else {
      // $videoComments[0]['snippet']['topLevelComment']['snippet']['textOriginal'] = 'updated';
      // // $videoCommentUpdateResponse = $youtube->commentThreads->update('snippet', $videoComments[0]);
    }

    // $htmlBody .= "<h3>Video Comments</h3><ul>";
    foreach ($videoComments as $comment) {
      if (strpos($comment['snippet']['topLevelComment']['snippet']['textOriginal'], 'love') == false) {
        // $htmlBody .= sprintf('<li>%s</li>', $comment['snippet']['topLevelComment']['snippet']['textOriginal']);
      }else{
        $youtube->comments->setModerationStatus($comment['id'], 'heldForReview');
      }
      
    }

    $htmlBody .= '</ul>';
  } catch (Google_Service_Exception $e) {
    // $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
    //     htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    // $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
    //     htmlspecialchars($e->getMessage()));
  }
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
  // $htmlBody = <<<END
  // <h3>Client Credentials Required</h3>
  // <p>
  //   You need to set <code>\$OAUTH2_CLIENT_ID</code> and
  //   <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  // <p>
END;
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;
  $authUrl = $client->createAuthUrl();
  // $htmlBody = <<<END
  // <h3>Authorization Required</h3>
  // <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
$name=$_GET['name'];
echo $name;
?>

<!-- <!doctype html>
<html>
<head>
<title>Insert, list and update top-level comments</title>
</head>
<body>
  <?=$htmlBody?>
</body>
</html> -->