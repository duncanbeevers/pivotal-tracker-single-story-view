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

  $.ISODateString = function(s) {
    d = new Date(s);
    function pad(n){
      return n<10 ? '0'+n : n;
    }
    return d.getUTCFullYear()+'-'+
      pad(d.getUTCMonth()+1)+'-'+
      pad(d.getUTCDate())+'T'+
      pad(d.getUTCHours())+':'+
      pad(d.getUTCMinutes())+':'+
      pad(d.getUTCSeconds())+'Z';
  };

  $.fn.extend({
    tmplWithCustomTags: function(data, helperTemplates) {
      var original_tags = {},
          tag = $.tmpl.tag,
          helpers = {},
          result;

      $.each(helperTemplates, function(helper_name, fn) {
        var json_helper_name = JSON.stringify(helper_name);

        original_tags[helper_name] = tag[helper_name];
        tag[helper_name] = {
          open: 'if($notnull_1){_.push($("<div>").append($item[' +
            JSON.stringify(helper_name) +
            ']($1)).html());}'
        };
        helpers[helper_name] = fn;
      });

      result = this.tmpl(data, helpers);

      $.each(helperTemplates, function(helper_name, _) {
        tag[helper_name] = original_tags[helper_name];
      });
      return result;
    }
  });

  $.pivotalTrackerSingleStoryView = (function(data) {
    // Massage data for templates
    data.story_type_class = "story_type_" + data.story_type;
    data.estimate_class = "estimate_" + data.estimate;
    data.url = data.url + '#from-single-story-view';
    
    // Set the title
    $('title').html($('#tmpl-title').tmpl(data));
    
    // Build the story
    var storyMarkup = $('#tmpl-story').tmplWithCustomTags(data, {
      time: function(time) { return $('#tmpl-time').tmpl($.ISODateString(time)); },
      embed_attachment: function(attachment) {
        var template = attachment.filename.match(img) ? '#tmpl-image-attachment' : '#tmpl-other-attachment';
        return $(template).tmpl(attachment);
      }
    });
    if (!data.description[0]) { storyMarkup.find('.description').hide(); }
    if (!data.notes) { storyMarkup.find('.comments').hide(); }
    if (!data.attachments) { storyMarkup.find('.attachments').hide(); }
    $('body').append(storyMarkup);
  
    // Make links of the urls
    $('.article pre').linkURLs();

    return this;
  });

})(jQuery);

