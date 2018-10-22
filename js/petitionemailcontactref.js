(function($){
  //Initial recipient system processing
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": "Email_Recipient_System"
  }).done(function(recipientSystemField) {
    //Page load
    recipient_system_contactref(recipientSystemField.id);
    //When the salutation type changes
    CRM.$("select[id*='custom_" + recipientSystemField.id + "']").change(function() {
      recipient_system_contactref(recipientSystemField.id);
    });
  });
})(CRM.$);

function recipient_system_contactref (fieldId){
  //Process the selected option
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": 'Recipient_Contact_Reference'
  }).done(function(recipientContactRefField) {
    //Show Contact Reference Field field
    if (CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val() == 'Contactref') {
      CRM.$("tr[class*='custom_" + recipientContactRefField.id + "']").show();
      recipient_single_hide_contactref(true);
    }
    if (CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val() != 'Contactref') {
      CRM.$("tr[class*='custom_" + recipientContactRefField.id + "']").hide();
    }
    if (CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val() == 'Single') {
      recipient_single_hide_contactref(false);
    }
  });
}

function recipient_single_hide_contactref(hide){
  CRM.api3('CustomField', 'get', {
    "return": ["id"],
    "name": {"IN":["Recipient_Name","Recipient_Email"]}
  }).done(function(recipientSingle) {
    if (hide) {
      CRM.$.each(recipientSingle.values, function(id,fieldId) {
        CRM.$("tr[class*='custom_" + id + "']").hide();
      });
    } else {
      CRM.$.each(recipientSingle.values, function(id, fieldId) {
        CRM.$("tr[class*='custom_" + id + "']").show();
      });
    }
  });
}
