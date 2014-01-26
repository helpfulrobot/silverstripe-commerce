<div class="commerce-content-container typography">
    <div class="commerce-product">

        <h1>$Title</h1>

        <div class="units-row">
            <div class="unit-50 commerce-product-images">
                <a href="$Images.First.SetRatioSize(900,550).Link">
                    $Images.First.PaddedImage(550,550)
                </a>

                <div class="units-row-end">
                    <% if $Images.Count > 1 %>
                        <div class="thumbs">
                            <% loop $Images %>
                                <% if not $First %>
                                    <a href="$SetRatioSize(900,550).Link">
                                        $PaddedImage(75,75)
                                    </a>
                                <% end_if %>
                            <% end_loop %>
                        </div>
                    <% end_if %>
                </div>
            </div>

            <div class="unit-50 commerce-product-summary">
                <p>
                    <span class="price label big label-green">
                        <span class="title"><% _t('Commerce.PRICE','Price') %>:</span>
                        <span class="value">
                            {$SiteConfig.Currency.HTMLNotation.RAW}
                            {$Price}
                        </span>
                    </span>

                    <% if $PackSize %>
                        <span class="packsize label big">
                            <span class="title bold"><% _t('Commerce.PACKSIZE','Pack Size') %>:</span>
                            <span class="value">{$PackSize}</span>
                        </span>
                    <% end_if %>

                    <% if $Weight %>
                        <span class="weight label big">
                            <span class="title bold"><% _t('Commerce.WEIGHT','Weight') %>:</span>
                            <span class="value">{$Weight}{$SiteConfig.Weight.Unit}</span>
                        </span>
                    <% end_if %>
                </p>

                <% if $Description %>
                    <div class="description">
                        <p>
                            $Description.Summary(50)
                            <a href="{$Top.Link()}#commerce-product-description" title="<% _t('Commerce.READMORE','Read More') %>: {$Title}">
                                <% _t('Commerce.READMORE','Read More') %>
                            </a>
                        </p>
                    </div>
                <% end_if %>

                $AddItemForm
            </div>
        </div>

        <div class="commerce-clear"></div>

        <div class="units-row">
            <div class="commerce-product-details">
                <% if $Description %>
                    <div id="commerce-product-description" class="description">
                        <h2><% _t('Commerce.DESCRIPTION','Description') %></h2>
                        $Description
                    </div>
                <% end_if %>

                <% if $Attributes %>
                    <div id="commerce-product-attributes" class="attributes">
                        <h2><% _t('Commerce.ATTRIBUTES','Attributes') %></h2>
                        <ul>
                            <% loop $Attributes %><li class="feature">
                                <strong>$Title:</strong> $Content
                            </li><% end_loop %>
                        </ul>
                    </div>
                <% end_if %>
            </div>
        </div>

    </div>
</div>

<% include SideBar %>