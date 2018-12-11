<?php

namespace Drupal\site_api_key\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Form\SiteInformationForm;

/**
 * Add SiteAPIKey field in form.
 */
class ExtendedSiteInformationForm extends SiteInformationForm {

  /**
   * SiteAPIKey added to Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $configs = $this->config('system.site');
    if (empty($configs->get('siteapikey'))) {
      $siteapikey = 'No API Key yet';
    }
    else {
      $siteapikey = $configs->get('siteapikey');
    }
    $form = parent::buildForm($form, $form_state);
    $form['site_information']['siteapikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site API Key'),
      '#default_value' => $siteapikey,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update Configuration'),
    ];
    return $form;
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('system.site');
    $this->configFactory->getEditable('system.site')
    // The site_description is retrieved from the submitted form values
    // and saved to the 'siteapikey' element of the system.site configuration.
      ->set('siteapikey', $form_state->getValue('siteapikey'))
    // Make sure to save the configuration.
      ->save();
    if ($config != $form_state->getValue('siteapikey') && !empty($form_state->getValue('siteapikey'))) {
      drupal_set_message($this->t('Site API Key has been saved with that value "@label"', ['@label' => $form_state->getValue('siteapikey')]));
    }
  }

}
