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

  $.ISODateString = function(d) {
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

  $.tmpl.tag.embed_attachment = {
    open: "if($notnull_1){_=_.concat($item.nest((attachment.filename.match(" +
      img +
      ") ? \"#tmpl-image-attachment\" : \"#tmpl-other-attachment\"),$1));}"
  };
  $.tmpl.tag.time = {
    open: "if($notnull_1){_=_.concat($item.nest(\"#tmpl-time\",$.ISODateString(new Date($1))));}"
  };

  $.pivotalTrackerSingleStoryView = (function(data) {
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

    return this;
  });

})(jQuery);

