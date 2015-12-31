<hr/>

<% if $SubTitle %>
    <h2>$SubTitle</h2>
<% end_if %>

$Content

<% if $Orders.exists %>
    <table class="width-100 table-hovered">
        <thead>
            <tr>
                <th><% _t("Commerce.Order","Order") %></th>
                <th><% _t("Commerce.Date","Date") %></th>
                <th><% _t("Commerce.Price","Price") %></th>
                <th><% _t("Commerce.Status","Status") %></th>
            </tr>
        </thead>
        <tbody>
            <% loop $Orders %>
                <tr>
                    <td><a href="{$Top.Link('order')}/{$ID}">$OrderNumber</a></td>
                    <td><a href="{$Top.Link('order')}/{$ID}">$Created.Nice</a></td>
                    <td><a href="{$Top.Link('order')}/{$ID}">$Total.Nice</a></td>
                    <td><a href="{$Top.Link('order')}/{$ID}">$TranslatedStatus</a></td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
<% end_if %>