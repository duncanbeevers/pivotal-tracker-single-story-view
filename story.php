<?php
require("pivotaltracker_rest.php");
$tracker = new PivotalTracker();

$token = $_GET['token'];
if (!$token) {
  $token = $_COOKIE['token'];
}
if (!$token) {
  if (array_key_exists('username', $_GET) && array_key_exists('password', $_GET)) {
    $token = $tracker->authenticate($_GET['username'], $_GET['password']);
    if ($token) {
      $json = array( 'success' => true, 'token' => $token );
    } else {
      $json = array( 'success' => false );
    }
  }
}

if ($token) {
  $tracker->token = $token;
  $story = $tracker->stories_get($_GET['project_id'], $_GET['story_id']);
}
?>

<?php if ($json) { ?>
<?= json_encode($json) ?>
<?php } else { ?>
<!doctype html>
<html>
  <head>
    <title></title>
    <style type="text/css">
body, pre { font-family: Arial, Verdana, sans-serif; }
pre {
  margin: 0;
  white-space: pre-wrap;
}
body {
  background-color: #9DA7AA;
}
.login {
  background-color: white;
  border-radius: 6px;
  margin: 0 auto;
  max-width: 600px;
  padding: 1em;
}
.login label {
  text-align: left;
  font-weight: bold;
}
.login input {
  font-size: 1.5em;
}
.login button {
  padding: 1em;
  border: none;
  border-radius: 6px;
  background-color: #0F5271;
  color: white;
  font-size: 18px;
  box-shadow: 0 1px 2px #808080;
  text-shadow: 0 1px 1px #303030;
  margin: 0;
}
.login button:active {
  margin-top: 1px;
  margin-bottom: -1px;
}
.login button:disabled {
  background-color: #606060;
  margin-top: 0;
  margin-bottom: 0;
}
.errors {
  background: transparent url(icons/error.png) no-repeat;
  background-position: 1em 1em;
  text-indent: 24px;
  padding: 1em;
  border: 1px solid #ff0000;
}
.story {
  padding: 1em;
  background-color: #F0ECCA;
  border-radius: 6px;
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
img.autolinked {
  margin-left: 0.5em;
  margin-right: 0.5em;
  max-height: 50px;
}
header {
  padding-bottom: 0.5em;
  margin-bottom: 1em;
  border-bottom: 1px solid rgba(128, 128, 128, 0.5);
  text-shadow: rgba(256, 256, 256, 0.2) 0px 1px 0px;
}
h1 {
  margin: 0;
  font-size: 20px;
}
h2 {
  margin: 0;
  font-size: 16px;
}
header h1 {
  display: inline-block;
}
time {
  color: #7C7B76;
  font-style: italic;
  font-size: 10px;
}
section {
  margin-bottom: 1.5em;
}
section.details {
  margin-bottom: 0;
}
section:last-child {
  margin-bottom: 0;
}
.itemized>div {
  padding: 0.5em;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  border-bottom: 1px solid rgba(192, 192, 192, 0.5);
  font-size: 14px;
  line-height: 15px;
}
.description pre {
  margin: 1em;
}
.itemized pre {
  padding-left: 1em;
}
.itemized img {
  max-height: 100px;
}
.comments img {
  max-height: 50px;
}
.sig {
  padding-bottom: 1em;
}
.story_type { padding-left: 32px; background-repeat: no-repeat; background-position: 8px 4px; }
.story_type_bug { background-image: url(icons/bug.png); }
.story_type_release { background-image: url(icons/release.png); }
.story_type_chore { background-image: url(icons/chore.png); }
.story_type_feature { background-image: url(icons/feature.png); }
.estimate_-1:after { content: " \2022  unestimated"; }
.estimate_0:after { content: " \2022  0 points"; }
.estimate_1:after { content: " \2022  1 point"; }
.estimate_2:after { content: " \2022  2 points"; }
.estimate_3:after { content: " \2022  3 points"; }
    </style>
  </head>
  <body>

<!-- templates -->
<script type="text/x-jquery-tmpl" id="tmpl-title">[${id}] ${name}</script>
<script type="text/x-jquery-tmpl" id="tmpl-time"><time datetime="${$data}">${$data}</time></script>

<script type="text/x-jquery-tmpl" id="tmpl-story">
<div class="story ${current_state}">

  <header>
    <h1 class="story_type ${story_type_class} ${estimate_class}">[<a href="${url}">${id}</a>] ${name}</h1>
  </header>

  <article class="article">

    <section class="description">
      <h2>Description</h2>
      <pre class="description">${description}</pre>
    </section>

    <section class="comments itemized">
      <h2>Comments</h2>
      {{each(i, comment) notes}}
      <div>
        <div class="sig">
          <span class="username">${author}</span>
          {{time noted_at}}
        </div>
        <pre>${text}</pre>
      </div>
      {{/each}}
    </section>

    <section class="attachments itemized">
      <h2>Attachments</h2>
      {{each(i, attachment) attachments}}
      <div>
        <div class="sig">
          <span class="username">${uploaded_by}</span>
          {{time uploaded_at}}
        </div>
        {{embed_attachment attachment}}
      </div>
      {{/each}}
    </section>

    <section class="details">
      <div class="requested_by">Requested by: ${requested_by}</div>
      <div class="created_at">Created at: {{time created_at}}</div> 
      <div class="updated_at">Updated at: {{time updated_at}}</div>
    </section>

  </article>
</div>
</script>

<script type="text/x-jquery-tmpl" id="tmpl-image-attachment">
<a href="${url}"><img src="${url}" alt="${filename}" />${filename}</a>
</script>

<script type="text/x-jquery-tmpl" id="tmpl-other-attachment">
<a href="${url}">${filename}</a>
</script>

<!-- behavior -->
<script src="//ajax.microsoft.com/ajax/jQuery/jquery-1.5.2.min.js" >type="text/javascript"></script>
<script src="//ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js" type="text/javascript"></script>
<script type="text/javascript" src="jquery.cookie.js"></script>

<script type="text/javascript">
// Auto-link urls
// Auto-embed image links
(function($) {
  var img = /(?:\.png)|(?:\.jpg)|(?:\.jpeg)|(?:\.gif)/i;
  $.fn.extend({
    linkURLs: function(options){
      var matchUrl = /[^"]\b((https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;

      function replaceUrl(url) {
        if (url.match(img)) {
          return "<a href=\"" + url + "\"><img class=\"autolinked\" src=\"" + url + "\" alt=\"" + url + "\"/>" + url + "</a>";
        } else {
          return "<a href=\"" + url + "\">" + url + "</a>";
        }
      }

      this.each(function(){
        $(this).html($(this).html().replace(matchUrl, replaceUrl));
      });

      return this;
    }
  });

  $.tmpl.tag.embed_attachment = {
    open: "if($notnull_1){_=_.concat($item.nest((attachment.filename.match(" +
      img +
      ") ? \"#tmpl-image-attachment\" : \"#tmpl-other-attachment\"),$1));}"
  };
  $.tmpl.tag.time = {
    open: "if($notnull_1){_=_.concat($item.nest(\"#tmpl-time\",$1));}"
  }

})(jQuery);
</script>

<?php if ($story && $token) { ?>
<script type="text/javascript">
  (function(data) {
    // Massage data for templates
    data.story_type_class = "story_type_" + data.story_type;
    data.estimate_class = "estimate_" + data.estimate;
    
    // Set the title
    $('title').html($('#tmpl-title').tmpl(data));
    
    // Build the story
    var storyMarkup = $('#tmpl-story').tmpl(data);
    if (!data.description[0]) { storyMarkup.find('.description').hide(); }
    if (!data.notes) { storyMarkup.find('.comments').hide(); }
    if (!data.attachments) { storyMarkup.find('.attachments').hide(); }
    $('body').append(storyMarkup);
  
    // Make links of the urls
    $('.article pre').linkURLs();
  
  })(<?= json_encode($story) ?>);
</script>

<?php } elseif ($token) { ?>


<?php } else { ?>

<script type="text/x-jquery-tmpl" id="tmpl-token-errors">
<section class="errors">
  There was an error retrieving your API token.
</section>
</script>

<form id="retrieve_token">
  <div class="login">
    <header>
      <h1>Pivotal Tracker Single Story View</h2>
    </header>
    <section>
      We don't currently know your Pivotal Tracker API Token.<br />
      Use this form to retrieve and store your token.
    </section>
    <section>
      <label for="username">Username</label><br />
      <input type="text" id="username" name="username" />
    </section>
    <section>
      <label for="password">Password</label><br />
      <input type="password" id="password" name="password" />
    </section>
    <section>
      <button type="submit">Retrieve Token</button>
    </section>
  </div>
</form>

<script type="text/javascript">
  (function() {
    $.ajaxPrefilter(function(options, localOptions, jqXHR) {
      var deferred = $.Deferred();

      jqXHR.done(function(json) {
        deferred[json.success ? 'resolve' : 'reject'](json);
      });

      jqXHR = deferred.promise(jqXHR);
      jqXHR.success = jqXHR.done;
      jqXHR.error = jqXHR.fail;
    });

    $('#retrieve_token').submit(function() {
      $('.login button').attr('disabled', true);
      $('.login .errors').remove();

      var req = $.ajax('', {
        data: {
          username: $('#username').val(),
          password: $('#password').val()
        },
        dataType: 'json',
        beforeSend: function() { $('.login button').attr('disabled', true); }
      });

      req.success(function(json) {
        $.cookie('token', json.token, { expires: 365 * 20, path: '/' });
        document.location.reload();
      });

      req.fail(function() {
        $('.login button').attr('disabled', false);
        $('.login').append($('#tmpl-token-errors').tmpl());
      });

      return false;
    });

  })();
</script>

<?php } ?>

  </body>
</html>

<?php } ?>


