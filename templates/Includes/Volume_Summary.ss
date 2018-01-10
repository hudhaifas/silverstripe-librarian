<% with $Book %>
    <% if $Title %><p class="dataobject-title"><a href="$ObjectLink">$Title</a></p><% end_if %>
<% end_with %>

<% if $BookTitle && $BookTitle != $BookName %>
    <p class="dataobject-title">$BookTitle</p>
<% end_if %>

<% loop Authors %>
    <p class="dataobject-info"><a href="$ObjectLink" >$FullName</a></p>
<% end_loop %>

<p class="dataobject-title"><%t Librarian.VOLUME_NUMBER "Volume Number {value}" value=$TheIndex %></p>

<% if $Edition || $PublishYear %><p class="dataobject-info"><%t Librarian.EDITION 'Edition' %>: $Edition ($PublishYear)</p><% end_if %>

<% if $Publisher && $Publisher.Name %>
<p class="dataobject-info">
    <%t Librarian.PUBLISHER 'Publisher' %>: <a title="$Title">$Publisher.Name</a>
</p>
<% end_if %>

<% if $Length %><p class="dataobject-info"><%t Librarian.LENGTH "Length" %>: $Length</p><% end_if %>
<% if $Collection %><p class="dataobject-info"><%t Librarian.COLLECTION 'Collection' %>: $Collection</p><% end_if %>
<% if $Shelf %><p class="dataobject-info"><%t Librarian.SHELF "Shelf" %>: $Shelf</p><% end_if %>
<% if $Format %><p class="dataobject-info"><%t Librarian.FORMAT 'Format' %>: $Format.Title</p><% end_if %>
<% if $Status %><p class="dataobject-info"><%t Librarian.STATUS "Status" %>: $Status</p><% end_if %>
