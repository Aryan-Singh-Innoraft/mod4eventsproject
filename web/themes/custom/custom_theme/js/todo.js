(function($, Drupal, once) {
  // console.log("Hello");
  Drupal.behaviors.todoMarkDone = {
    attach: function (context, settings) {
      // console.log("Behavior attached");
      once('todoMarkDone', '.todo-done', context).forEach(function(el) {
        $(el).on('click', function(e) {
        // $('.todo-done', context).once('todoMarkDone').on('click', function (e) {
        e.preventDefault();

        var nid = $(this).data('item');
        // var elementSettings = {
        //   url: Drupal.url('todo/mark-done/' + nid),
        //   event: 'click',
        //   progress: { type: 'throbber' }
        // };

        // var ajax = new Drupal.Ajax(false, this, elementSettings);
        // ajax.execute(); 
        Drupal.ajax({
            url: Drupal.url('todo/mark-done/' + nid),
            event: 'click',
            progress: { type: 'throbber' }
          }).execute();
        });
      });
    }
  };
})(jQuery, Drupal, once);
