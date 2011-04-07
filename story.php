<?php

// Helper functions
function d($ds) {
  return htmlspecialchars(strftime("%l:%M %P %a %b %e %Y", strtotime($ds)), ENT_COMPAT);
}
function q($s) {
  return htmlspecialchars($s, ENT_QUOTES);
}
function h($s) {
  return htmlspecialchars($s, ENT_COMPAT);
}

require("pivotaltracker_rest.php");
$tracker = new PivotalTracker($_GET['token']);

$story = $tracker->stories_get($_GET['project_id'], $_GET['story_id']);
$description = $story['description'];
$notes = $story['notes'];
if ($notes['note'][0]) { $notes = $notes['note']; }
$attachments = $story['attachments'];
if ($attachments['attachment'][0]) { $attachments = $attachments['attachment']; }
$estimate = $story['estimate'];
if ($story['story_type']) {
  $icon_path = 'icons/'.$story['story_type'].'.png';
}
?>
<!doctype html>
<html>
  <head>
    <title>[<?= h($story['id']) ?>] <?= h($story['name']) ?></title>
    <style type="text/css">
body, pre { font-family: Arial, Verdana, sans-serif; }
pre {
  margin: 0;
  white-space: pre-wrap;
}
body {
  background-color: #9DA7AA;
}
.story {
  padding: 1em;
  background-color: #F0ECCA;
}
.story.accepted {
  background-color: #CFE4C7;
}
.story.unstarted {
  background-color: #F1F1F1;
}
.story.unscheduled {
  background-color: #DCECF3;
}
img {
  vertical-align: baseline;
}
img.story_type {
  margin-right: 0.5em;
}
img.autolinked {
  margin-right: 0.5em;
  max-height: 75px;
}
h1 {
  margin: 0 0 0.5em 0;
  font-size: 24px;
  display: inline-block;
}
h2 {
  margin: 0;
  font-size: 18px;
}
time {
  color: #7C7B76;
  font-style: italic;
  font-size: 10px;
}
.description {
  margin: 1em;
}
section {
  margin-bottom: 1.5em;
}
.itemized>div {
  padding: 0.5em;
  border-top: 1px solid #C0CFEB;
  font-size: 14px;
  line-height: 15px;
}
.itemized pre {
  padding-left: 1em;
}
.itemized img {
  max-height: 100px;
}
.itemized img.autolinked {
  max-height: 50px;
}
.sig {
  padding-bottom: 1em;
}
.raw {
  display: none;
}
.estimate_-1:after { content: " \2022  unestimated"; }
.estimate_0:after { content: " \2022  0 points"; }
.estimate_1:after { content: " \2022  1 point"; }
.estimate_2:after { content: " \2022  2 points"; }
.estimate_3:after { content: " \2022  3 points"; }
    </style>
  </head>
  <body>

    <div class="story <?= q($story['current_state']) ?>">
      <header>
      <?php if ($icon_path) { ?>
      <img class="story_type" src="<?= q($icon_path) ?>" alt="<?= q($story['story_type']) ?>" />
      <?php } ?>
        <h1 class="estimate_<?= q($estimate) ?>">[<a href="<?= q($story['url']) ?>"><?= h($story['id']) ?></a>] <?= h($story['name']) ?></h1>
      </header>

      <article id="article">
        <section>
          <h2>Description</h2>
          <pre class="description"><? if ($description) { ?><?= h($description) ?><?php } ?></pre>
        </section>

        <?php if ($notes) { ?>
        <section class="itemized">
	  <h2>Comments</h2>
          <?php foreach ($notes as $_ => $note) { ?>
          <div>
            <div class="sig">
              <span class="username"><?= h($note['author']) ?></span>
              <time><?= d($note['noted_at']) ?></time>
            </div>
            <pre><?= h($note['text']) ?></pre>
          </div>
          <?php } ?>
        </section>
        <?php } ?>

        <?php if ($attachments) { ?>
        <section class="itemized">
          <h2>Attachments</h2>
          <?php foreach ($attachments as $_ => $attachment) { ?>
          <div>
            <div class="sig">
              <span class="username"><?= h($attachment['uploaded_by']) ?></span>
              <time><?= d($attachment['uploaded_at']) ?></time>
            </div>
            <?php
            if (preg_match("/(?:\.png)|(?:\.gif)|(?:\.jpg)|(?:\jpeg)/i", $attachment['filename'])) {
              $attachment_link_content = "<img src=\"".q($attachment['url'])."\" alt=\"".q($attachment['filename'])."\" />";
            } else {
              $attachment_link_content = h($attachment['filename']);
            } ?>
            <a href="<?= q($attachment['url']); ?>"><?= $attachment_link_content ?></a>
          </div>
          <? } ?>
        </section>
        <?php } ?>

        <div class="details">
          <div class="requested_by">Requested by: <?= h($story['requested_by']) ?></div>
          <div class="created_at">Created at: <time><?= d($story['created_at']) ?></time></div>
          <div class="updated_at">Updated at: <time><?= d($story['updated_at']) ?></time></div>
        </div>
      </article>

    </div>

    <a href="#" class="toggle_raw">raw</a>

    <div class="raw">
      <pre><?php print_r($story) ?></pre>
    </div>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript">

(function($) {
  $.fn.extend({
    linkURLs: function(options){
      var url_matcher = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig,
          img = /(?:\.png)|(?:\.jpg)|(?:\.jpeg)|(?:\.gif)/i;
      this.each( function(){
var t = $(this);
        t.html( t.html().replace(url_matcher, function(url) {
        if (url.match(img)) {
          return "<a href=\"" + url + "\"><img class=\"autolinked\" src=\"" + url + "\" alt=\"" + url + "\"/>" + url + "</a>";
        } else {
          return "<a href=\"" + url + "\">" + url + "</a>";
        }
}) );
      });
      return this;
    }
  });
})(jQuery);

$('#article pre').linkURLs();
$('.toggle_raw').click(function(e) {
  $('#article,.raw').toggle();
  return false;
});

    </script>
  </body>
</html>

