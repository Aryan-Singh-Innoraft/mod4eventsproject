<?php

namespace Drupal\site_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Site api settings for this site.
 */
final class BearerTokenForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'site_api_bearer_token';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['site_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['bearer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer Token'),
      '#default_value' => $this->config('site_api.settings')->get('bearer'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('site_api.settings')
      ->set('bearer', $form_state->getValue('bearer'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
