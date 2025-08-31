(function ($, Drupal, once) {
  Drupal.behaviors.paypalOnSubmit = {
    attach: function (context, settings) {
      once('paypal-submit', '.js-form-submit', context).forEach(function (element) {
        $(element).on('click', function (e) {
          e.preventDefault();
          var money = $('#edit-amount').val();
          paypal.Buttons({
            createOrder: function(data, actions) {
              return actions.order.create({
                purchase_units: [{
                  amount: { value: money } 
                }]
              });
            },
            onApprove: function(data, actions) {
              return actions.order.capture().then(function(details) {
                alert('Transaction completed by ' + details.payer.name.given_name + '!');
              });
            }
          }).render('#paypal-button-container');

          $('#paypal-button-container').show();
        });
      });
    }
  };
})(jQuery, Drupal, once);
