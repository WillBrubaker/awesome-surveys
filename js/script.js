jQuery(document).ready(function($){
  $('form.answer-survey').on('submit', function(e){
    e.preventDefault();
    $.post(wwm_awesome_surveys.ajaxurl, $(this).serializeArray(), function(data){
      console.log(data);
    });
  });
});