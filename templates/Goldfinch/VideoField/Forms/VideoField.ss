<div
  class="goldfinchicon"
  data-goldfinch-video-field="{$Name}"
  data-goldfinch-icon-config="{$IconsConfigJSON}"
  data-goldfinch-icon-source="{$IconsListJSON}"
>

  $KeyField.SmallFieldHolder
  $DataField.SmallFieldHolder

  <div class="goldfinchicon__wrapper goldfinchicon__wrapper--selected" data-goldfinch-icon-selected>
    $CurrentIcons
  </div>

  <div data-goldfinch-icon-loader>
  <button type="button" class="btn btn-primary tool-button font-icon-down-circled">Load all icons ($IconsList.Count)</button>
  </div>

  <div class="field text goldfinchicon__search goldfinchicon__hide" data-goldfinch-icon-search>
    <input type="text" class="text" placeholder="Search icon ...">
  </div>

  <div class="goldfinchicon__wrapper goldfinchicon__wrapper--selections goldfinchicon__hide" data-goldfinch-icon-selection>
  </div>

</div>
