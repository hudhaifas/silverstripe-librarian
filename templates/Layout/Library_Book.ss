<% with Book %>
<div class="col-md-8 col-lg-8">
    <div class="row">
        <h1>$Title</h1>
        <% loop Authors %>
        <h4><a href="$Link" >$FullName</a></h4>
        <% end_loop %>

        <% if $Subject %><span class="information"><%t Librarian.SUBJECT 'Subject' %>: $Subject</span><% end_if %>
        <% if $OriginalPublish %><span class="information"><%t Librarian.ORIGINAL_PUBLISH 'Original Publish' %>: $OriginalPublish</span><% end_if %>
        <% if $Language %><span class="information"><%t Librarian.LANGUAGE 'Language' %>: $Language</span><% end_if %>

        <!-- Catagories -->
        <% if Categories %>
        <div>
            <h5><%t Librarian.CATEGORIES 'Categories' %></h5>
            <span class="information">
                <% loop Categories %>
                <a href="$Link">$Title</a><% if not Last %><%t Librarian.COMMA ',' %> <% end_if %>
                <% end_loop %>
            </span>
        </div>
        <% end_if %>

    </div>

    <!-- Copies List -->
    <% if BookCopies %>
    <% loop BookCopies %>
    <div class="row">
        <% if $Title && $BookName != $Title %>
        <div class="col-md-12">
            <h4>$Title</h4>
        </div>
        <% end_if %>

        <div class="col-lg-3 col-sm-3 col-xs-4 col-xxs-12">
            <% include Images_Book %>
        </div>

        <div class="col-lg-9 col-sm-9 col-xs-8  col-xxs-12">
            <% if $ISBN %><span class="information"><%t Librarian.ISBN 'Subject' %>: $ISBN</span><% end_if %>
            <% if $Edition || $PublishYear %><span class="information"><%t Librarian.EDITION 'Edition' %>: $Edition ($PublishYear)</span><% end_if %>
            <% if $Publisher && $Publisher.Name %>
            <span class="information">
                <%t Librarian.PUBLISHER 'Publisher' %>: <a title="$Publisher.Name">$Publisher.Title</a>
            </span>
            <% end_if %>
            <% if $Collection %><span class="information"><%t Librarian.COLLECTION 'Collection' %>: $Collection</span><% end_if %>
            <% if $Format %><span class="information"><%t Librarian.FORMAT 'Format' %>: $Format.Title</span><% end_if %>
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
                        <td><a href="$Link">$TheIndex</a></td>
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
    <% end_if %>

    <% if $Overview %>
    <div class="row">
        <div>
            <h4><%t Librarian.BOOK_OVERVIEW "Book Overview" %></h4>

            <div class="resp-tabs-container">
                $Overview
            </div>
        </div>
    </div>
    <% end_if %>
</div>

<div class="col-md-4 col-lg-4">
    <% if $Related %>
        <h4><%t Librarian.ALSO_READ "Also Read" %></h4>

        <% loop $Related.Limit(4) %>
            <% include Related_Book %>
        <% end_loop %>
    <% end_if %>
</div>
<% end_with %>