<% with Volume %>
<div class="col-md-9">
    <div class="row">
        <div class="col-lg-5 col-md-6 col-xs-12">
            <% include Images_Volume %>
        </div>

        <div class="col-lg-7 col-md-6 col-xs-12">
            <% with $Book %>
            <% if $Title %><h1><a href="$Link">$Title</a></h1><% end_if %>
            <% end_with %>

            <% if $BookTitle && $BookTitle != $BookName %>
            <h1>$BookTitle</h1>
            <% end_if %>

            <% loop Authors %>
            <h3><a href="$Link" >$FullName</a></h3>
            <% end_loop %>

            <h4><%t Librarian.VOLUME_NUMBER "Volume Number {value}" value=$TheIndex %></h4>

            <% if $Edition || $PublishYear %><p class="information"><%t Librarian.EDITION 'Edition' %>: $Edition ($PublishYear)</p><% end_if %>

            <% if $Publisher && $Publisher.Name %>
            <p class="information">
                <%t Librarian.PUBLISHER 'Publisher' %>: <a title="$Title">$Publisher.Name</a>
            </p>
            <% end_if %>

            <% if $Length %><p class="information"><%t Librarian.LENGTH "Length" %>: $Length</p><% end_if %>
            <% if $Collection %><p class="information"><%t Librarian.COLLECTION 'Collection' %>: $Collection</p><% end_if %>
            <% if $Shelf %><p class="information"><%t Librarian.SHELF "Shelf" %>: $Shelf</p><% end_if %>
            <% if $Format %><p class="information"><%t Librarian.FORMAT 'Format' %>: $Format.Title</p><% end_if %>
            <% if $Status %><p class="information"><%t Librarian.STATUS "Status" %>: $Status</p><% end_if %>
        </div>

    </div>

    <div class="row">
        <div>
            <% if $Overview %>
            <h4><%t Librarian.BOOK_OVERVIEW "Book Overview" %></h4>

            <div class="resp-tabs-container">
                $Overview
            </div>
            <% end_if %>
        </div>
    </div>
</div>

<div class="col-md-3">
    <% if $Related %>
    <h3 class="m_1"><%t Librarian.ALSO_READ "Also Read" %></h3>

    <% loop $Related.Limit(4) %>
    <% include Related_Volume %>
    <% end_loop %>
    <% end_if %>
</div>
<% end_with %>