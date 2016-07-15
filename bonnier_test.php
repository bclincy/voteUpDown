<?php
/*
 * This script expects these parameters:
 * - article_id
 * - score
 *
 * Example bonnier_test.php?article_id=1&vote_value=1
 */

include_once('includes/db.php');
//Vars with Default values
$fields = array('article_id', 'vote_value');
$error[0] ='Opps, Something happen Please try vote up or down links';
$article_id = 1;
$vote_value = 0;
$exitsInDb = false;

// Security filter the data and generate error message
if (isset($_GET[$fields[0]])) {

  if (!verifyData($fields)) {
    header('location: '. $_SERVER['SCRIPT_NAME'] . '?error=0');
  }
  $article_id = $_REQUEST['article_id'];
  $vote_value = $_REQUEST['vote_value'];
  $hasVoteFlag = true;

}
//Get the Score
getCurrentScore($db, $article_id, $article_score, $exitsInDb);
if ($exitsInDb === false && isset($hasVoteFlag) && $hasVoteFlag === true) {
  $article_score = 0 + $vote_value;
  intializeScore($db, $article_id, $article_score );
} elseif ($exitsInDb === false && !isset($hasVoteFlag)) {
  intializeScore($db, $article_id, $article_score, 0);
} elseif ($exitsInDb === true  && isset($hasVoteFlag) && $hasVoteFlag === true) {
  $article_score = $article_score + $vote_value;
  updateScore($db, $article_id, $article_score);
}


if (isset($_REQUEST['error']) && $_REQUEST['error'] == 0 ) {
  echo '<h1>' . $error[0] . '</h2>';
}
$widget  = '<div class="voting_widget"><h1>Voting on Article '. $article_id . '</h1>';
$widget .= '<a href="bonnier_test.php?article_id='.$article_id.'&vote_value=1">Vote Up</a>';
$widget .= ' ';
$widget .= '<a href="bonnier_test.php?article_id='.$article_id.'&vote_value=-1">Vote Down</a>';
$widget .= ' ';
$widget .= 'Score: '. $article_score;
$widget .= '</div>';

print $widget;

function verifyData(array $field)
{
  foreach ($field as $value) {
    if (!filter_input(INPUT_GET, $value, FILTER_VALIDATE_INT)) {
      return false;
    }
  }

  return true;
}

function getCurrentScore(pdo $db, $article_id, &$article_score, &$existsInDb)
{
  $result = $db->prepare("SELECT score FROM vote WHERE article_id = :article_id");
  $result->execute(array(':article_id' => $article_id));
  if ($result->rowCount() != 0) {
    $article_score = $result->fetchColumn();
    $existsInDb = true;
  } else {
    $article_score = 0;
    $existsInDb = false;
  }
}

function intializeScore(pdo $db, $article_id, $vote_value) {
  $sql = "INSERT INTO vote (article_id, score) VALUES (:article_id, :vote_value)";
  $result = $db->prepare($sql);
  $result->execute(array(':article_id' => $article_id, ':vote_value' => $vote_value));
  if ($result->rowCount() < 1) {
    throw new Exception('Oops: There was problem saving your vote. Please try later');
  }
}

function updateScore(pdo $db, $article_id, $article_score) {
  $result = $db->prepare('Update vote SET score = :article_score WHERE article_id = :article_id');
  $result->execute(array(':article_id' => $article_id, ':article_score' => $article_score));
  if ($result->rowCount() < 1) {
    throw new Exception('Oops: There was problem saving your vote. Please try later');
  }
}


function getScore(pdo $db, $article_id = 1, $vote_value = 0) {
  $result = $db->prepare("SELECT score FROM vote WHERE article_id = :article_id");
  $result->execute(array(':article_id' => $article_id));
  if ($result->rowCount() != 0) {
    $article_score = $result->fetchColumn();
  }
   else {
    $sql = "INSERT INTO vote (article_id, score) VALUES (:article_id, :vote_value) ON DUPLICATE KEY UPDATE score = score + :vote_value";
    $result = $db->prepare($sql);
    $result->execute(array(':article_id'=>$article_id, ':vote_value'=>$vote_value));
    $result = $db->prepare("SELECT score FROM vote WHERE article_id = :article_id");
    $result->execute(array(':article_id' => $article_id));
    $article_score = $result->fetchColumn();
  }

  return $article_score;
}
