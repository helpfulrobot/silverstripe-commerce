<p class="units-row">
    <% _t('CommerceAccount.NOACCOUNT',"Don't have an account") %>?
</p>

<p class="units-row">
    <a href="{$BaseHref}users/register?BackURL={$Link}" class="btn text-centered unit-push-right width-100">
        <% _t("Users.REGISTER", "Register") %>
    </a>
</p>

<p class="units-row text-centered"><strong><% _t('CommerceAccount.OR',"Or") %></strong></p>

<p class="units-row">
    <a href="{$Link('billing')}" class="btn text-centered unit-push-right width-100">
        <% _t('CommerceAccount.CONTINUEGUEST',"Continue as a Guest") %>
    </a>
</p>