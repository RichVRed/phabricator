<?php

/**
 * This is a bare HTML page view which has access to Phabricator page
 * infrastructure like Celerity, but no content or builtin static resources.
 * You basically get a valid HMTL5 document and an empty body tag.
 *
 * @concrete-extensible
 */
class PhabricatorBarePageView extends AphrontPageView {

  private $request;
  private $controller;
  private $frameable;
  private $deviceReady;

  private $bodyContent;

  public function setController(AphrontController $controller) {
    $this->controller = $controller;
    return $this;
  }

  public function getController() {
    return $this->controller;
  }

  public function setRequest(AphrontRequest $request) {
    $this->request = $request;
    return $this;
  }

  public function getRequest() {
    return $this->request;
  }

  public function setFrameable($frameable) {
    $this->frameable = $frameable;
    return $this;
  }

  public function getFrameable() {
    return $this->frameable;
  }

  public function setDeviceReady($device_ready) {
    $this->deviceReady = $device_ready;
    return $this;
  }

  public function getDeviceReady() {
    return $this->deviceReady;
  }

  protected function willRenderPage() {
    // We render this now to resolve static resources so they can appear in the
    // document head.
    $this->bodyContent = phutil_implode_html('', $this->renderChildren());
  }

  protected function getHead() {
    $viewport_tag = null;
    if ($this->getDeviceReady()) {
      $viewport_tag = phutil_tag(
        'meta',
        array(
          'name' => 'viewport',
          'content' => 'width=device-width, '.
                       'initial-scale=1, '.
                       'user-scalable=no',
        ));
    }

    $mask_icon = phutil_tag(
      'link',
      array(
        'rel' => 'mask-icon',
        'color' => '#3D4B67',
        'href' => celerity_get_resource_uri(
          '/rsrc/favicons/mask-icon.svg'),
      ));

    $icon_tag_76 = phutil_tag(
      'link',
      array(
        'rel' => 'apple-touch-icon',
        'href' => celerity_get_resource_uri(
          '/rsrc/favicons/apple-touch-icon-76x76.png'),
      ));

    $icon_tag_120 = phutil_tag(
      'link',
      array(
        'rel' => 'apple-touch-icon',
        'sizes' => '120x120',
        'href' => celerity_get_resource_uri(
          '/rsrc/favicons/apple-touch-icon-120x120.png'),
      ));

    $icon_tag_152 = phutil_tag(
      'link',
      array(
        'rel' => 'apple-touch-icon',
        'sizes' => '152x152',
        'href' => celerity_get_resource_uri(
          '/rsrc/favicons/apple-touch-icon-152x152.png'),
      ));

    $favicon_tag = phutil_tag(
      'link',
      array(
        'id' => 'favicon',
        'rel' => 'shortcut icon',
        'href' => celerity_get_resource_uri(
          '/rsrc/favicons/favicon.ico'),
      ));

    $referrer_tag = phutil_tag(
      'meta',
      array(
        'name' => 'referrer',
        'content' => 'never',
      ));

    $response = CelerityAPI::getStaticResourceResponse();

    if ($this->getRequest()) {
      $viewer = $this->getRequest()->getViewer();
      if ($viewer) {
        $postprocessor_key = $viewer->getUserSetting(
          PhabricatorAccessibilitySetting::SETTINGKEY);
        if (strlen($postprocessor_key)) {
          $response->setPostProcessorKey($postprocessor_key);
        }
      }
    }

    return hsprintf(
      '%s%s%s%s%s%s%s%s',
      $viewport_tag,
      $mask_icon,
      $icon_tag_76,
      $icon_tag_120,
      $icon_tag_152,
      $favicon_tag,
      $referrer_tag,
      $response->renderResourcesOfType('css'));
  }

  protected function getBody() {
    return $this->bodyContent;
  }

  protected function getTail() {
    $response = CelerityAPI::getStaticResourceResponse();
    return $response->renderResourcesOfType('js');
  }

}
