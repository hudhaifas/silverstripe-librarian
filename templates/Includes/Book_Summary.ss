<p class="dataobject-title">$Title</p>
<% loop Authors %>
    <p class="dataobject-info"><a href="$ObjectLink" >$FullName</a></p>
<% end_loop %>

<% if $Subject %><p class="dataobject-info"><%t Librarian.SUBJECT 'Subject' %>: $Subject</p><% end_if %>
<% if $OriginalPublish %><p class="dataobject-info"><%t Librarian.ORIGINAL_PUBLISH 'Original Publish' %>: $OriginalPublish</p><% end_if %>
<% if $Language %><p class="dataobject-info"><%t Librarian.LANGUAGE 'Language' %>: $Language</p><% end_if %>

<!-- Catagories -->
<% with Categories %>
    <% if $Count %>
        <p class="dataobject-info" style="margin-top: 12px;">
            <%t Librarian.CATEGORIES 'Categories' %>: 
            <% loop $Me %>
                <a href="$ObjectLink">$Title</a>
                <% if not Last %>|<% end_if %>
            <% end_loop %>
        </p>
    <% end_if %>
<% end_with%>