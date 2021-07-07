(function($){
  //Initial recipient system processing
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": "Email_Recipient_System"
  }).done(function(recipientSystemField) {
    // Set based on initial value.
    update_contactref_recipient_display(recipientSystemField.id);
    // Update when the value changes.
    CRM.$("select[id*='custom_" + recipientSystemField.id + "']").change(function() {
      update_contactref_recipient_display(recipientSystemField.id);
    });
  });
})(CRM.$);

function update_contactref_recipient_display (fieldId){
  // Update display based on selected value.
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": 'Recipient_Contact_Reference'
  }).done(function(recipientContactRefField) {
    //Show Contact Reference Field field
    if (CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val() == 'Contactref') {
      CRM.$("tr[class*='custom_" + recipientContactRefField.id + "']").show();
    }
    else {
      CRM.$("tr[class*='custom_" + recipientContactRefField.id + "']").hide();
    }
  });
}

