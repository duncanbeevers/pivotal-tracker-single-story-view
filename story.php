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
    <link type="text/css" rel="stylesheet" href="styles.css"</link>
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
<script type="text/javascript" src="//ajax.microsoft.com/ajax/jQuery/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="//ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>
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

<script type="text/javascript" src="pivotalTrackerRetrieveToken.js"></script>


<?php } ?>

  </body>
</html>

<?php } ?>


