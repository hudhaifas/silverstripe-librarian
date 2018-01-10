<!-- Copies List -->
<% loop BookCopies %>
<div class="row">
    <% if $Title && $BookName != $Title %>
    <div class="col-md-12">
        <p class="dataobject-title">$Title</p>
    </div>
    <% end_if %>

    <div class="col-lg-2 col-sm-2 col-xs-4 col-xxs-12">
        <div class="thumbnail text-center col-sm-12 dataobject-image">
            <% include List_Image %>
        </div>
    </div>

    <div class="col-lg-10 col-sm-10 col-xs-8  col-xxs-12">
        <% if $ISBN %><p class="dataobject-info"><%t Librarian.ISBN 'Subject' %>: $ISBN</p><% end_if %>
        <% if $Edition || $PublishYear %><p class="dataobject-info"><%t Librarian.EDITION 'Edition' %>: $Edition ($PublishYear)</p><% end_if %>
        <% if $Publisher && $Publisher.Name %>
        <p class="dataobject-info">
            <%t Librarian.PUBLISHER 'Publisher' %>: <a title="$Publisher.Name">$Publisher.Title</a>
        </p>
        <% end_if %>
        <% if $Collection %><p class="dataobject-info"><%t Librarian.COLLECTION 'Collection' %>: $Collection</p><% end_if %>
        <% if $Format %><p class="dataobject-info"><%t Librarian.FORMAT 'Format' %>: $Format.Title</p><% end_if %>
    </div>

    <div class="col-md-12">
        <table class="table table-hover table-condensed">
            <thead>
                <tr>
                    <th><%t Librarian.VOLUME "Volume" %></th>  
                    <th><%t Librarian.LENGTH "Length" %></th>  
                    <th><%t Librarian.SHELF "Shelf" %></th>  
                    <th><%t Librarian.STATUS "Status" %></th>  
                </tr>
            </thead>
            <tbody>
                <% loop $BookVolumes %>
                <tr>
                    <td><a href="$ObjectLink">$TheIndex</a></td>
                    <td>$Length</td>
                    <td>$Shelf</td>
                    <td>$Status</td>
                </tr>
                <% end_loop %>
            </tbody>
        </table>
    </div>
</div>
<% end_loop %>
