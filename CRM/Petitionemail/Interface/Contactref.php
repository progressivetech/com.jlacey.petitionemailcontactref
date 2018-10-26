<?php
/**
 * @file
 * Contactref email interface.
 */

/**
 * An interface to send a single email.
 *
 * @extends CRM_Petitionemail_Interface
 */
class CRM_Petitionemail_Interface_Contactref extends CRM_Petitionemail_Interface {

  /**
   * Instantiate the delivery interface.
   *
   * @param int $surveyId
   *   The ID of the petition.
   */
  public function __construct($surveyId) {
    parent::__construct($surveyId);

    $this->neededFields[] = 'Support_Subject';
    $this->neededFields[] = 'Support_Message';
    $this->neededFields[] = 'Recipient_Contact_Reference';

    $fields = $this->findFields();
    $petitionemailval = $this->getFieldsData($surveyId);

    foreach ($this->neededFields as $neededField) {
      if (empty($fields[$neededField]) || empty($petitionemailval[$fields[$neededField]])) {
        // FIXME: provide something more meaningful.
        return;
      }
    }
    // If all needed fields are found, the system is no longer incomplete.
    $this->isIncomplete = FALSE;
  }

  /**
   * Take the signature form and send an email to the recipient.
   *
   * @param CRM_Campaign_Form_Petition_Signature $form
   *   The petition form.
   */
  public function processSignature($form) {
    $ufFields = array('subject' => 'Support_Subject', 'message' => 'Support_Message');
    // Get the message.
    foreach($ufFields as $type => $name) {
      $fieldName = $name . '_Field';
      $field = $this->findUFField("$fieldName");
      if ($field === FALSE) {
        return;
      }
      $$type = empty($form->_submitValues[$field]) ? $this->petitionEmailVal[$this->fields["$name"]] : $form->_submitValues[$field];
      // If message is left empty and no default message, don't send anything.
      if (empty($$type)) {
        return;
      }
    }
    $contactRefIdField = $this->fields['Recipient_Contact_Reference']  . '_id';
    $contactRefId = $this->petitionEmailVal[$contactRefIdField];
    $contactRef = civicrm_api3('Contact', 'getsingle', ['return' => ["display_name", "email"],'id' => $contactRefId,]);

    // Setup email message:
    $mailParams = array(
      'groupName' => 'Activity Email Sender',
      'from' => $this->getSenderLine($form->_contactId),
      'toName' => $contactRef['display_name'],
      'toEmail' => $contactRef['email'],
      'subject' => $subject,
      'text' => strip_tags($message),
      'html' => $message,
    );

    if (!CRM_Utils_Mail::send($mailParams)) {
      CRM_Core_Session::setStatus(ts('Error sending message to %1', array('domain' => 'com.aghstrategies.petitionemail', 1 => $mailParams['toName'])));
    }
    else {
      CRM_Core_Session::setStatus(ts('Message sent successfully to %1', array('domain' => 'com.aghstrategies.petitionemail', 1 => $mailParams['toName'])));
    }
    parent::processSignature($form);
  }

  /**
   * Prepare the signature form with the default message.
   *
   * @param CRM_Campaign_Form_Petition_Signature $form
   *   The petition form.
   */
  public function buildSigForm($form) {
    $defaults = $form->getVar('_defaults');

    $ufFields = array('Support_Subject', 'Support_Message');
    // Get the message.
    foreach($ufFields as $name) {
      $fieldName = $name . '_Field';
      $field = $this->findUFField("$fieldName");
      if ($field === FALSE) {
        return;
      }
      if (empty($this->petitionEmailVal[$this->fields["$name"]])) {
        return;
      }
      else {
        $defaultValue = $this->petitionEmailVal[$this->fields["$name"]];
      }

      foreach ($form->_elements as $element) {
        if ($element->_attributes['name'] == $field) {
          if ($element->_type == 'text') {
            $element->_attributes['value'] = $defaultValue;
          } elseif ($element->_type == 'textarea') {
            $element->_value = $defaultValue;
          }
        }
      }
      $defaults[$field] = $form->_defaultValues[$field] = $defaultValue;
    }
    $form->setVar('_defaults', $defaults);
  }

}
