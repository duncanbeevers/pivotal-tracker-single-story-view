<?php
require("pivotaltracker_rest.php");
$tracker = new PivotalTracker();

$token = $_GET['token'];
if (!$token) {
  $token = $_COOKIE['token'];
}
if (!$token) {
  if ($_GET['username'] && $_GET['password']) {
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
  margin-right: 0.25em;
}
img.autolinked {
  margin-left: 0.5em;
  margin-right: 0.5em;
  max-height: 50px;
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
section {
  margin-bottom: 1.5em;
}
section.details {
  margin-bottom: 0;
}
.itemized>div {
  padding: 0.5em;
  border-top: 1px solid #C0CFEB;
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
    <img class="story_type" src="${icon_src}" alt="${story_type}" />
    <h1 class="${estimate_class}">[<a href="${url}">${id}</a>] ${name}</h1>
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

<script type="text/javascript">
// Auto-link urls
// Auto-embed image links
(function($) {
  var img = /(?:\.png)|(?:\.jpg)|(?:\.jpeg)|(?:\.gif)/i;
  $.fn.extend({
    linkURLs: function(options){
      var matchUrl = /([^"]\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;

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

  jQuery.cookie = function (key, value, options) {
    
    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }
        
        value = String(value);
        
        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
  };

})(jQuery);

<?php if ($story) { ?>

  (function(data) {
    // Massage data for templates
    data.icon_src = "icons/" + data.story_type + ".png";
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

<?php } else { ?>

  (function() {
    var username, password;
    username = prompt("One-time setup\nPlease enter your Pivotal Tracker username");
    if (username) {
      password = prompt("One-time setup\nPlease enter your Pivotal Tracker password");
    }

    if (username && password) {
      $.ajax('', { data: { username: username, password: password }, dataType: 'json' }).success(function(data) {
        if (data.success) {
          $.cookie('token', data.token, { expires: 365 * 20 } );
          document.location.reload();
        } else {
          alert('Set-up Failed');
        }
      });
    }
  })();

<?php } ?>


    </script>
  </body>
</html>

<?php } ?>

