<% include Menu_Side %>

<article class="col-md-9">

    <div class="row">
        $SearchCatalog
    </div>

    <% if $Query %>
    <div class="row">
        <%t Librarian.SEARCH_QUERY 'You searched for &quot;{value}&quot;' value=$Query %>
    </div>
    <% end_if %>

    <div class="row">
        <% if $Results %>
            <% loop $Results %>
            <div class="col-md-4">
                <a href="$Link">
                    <div class="thumbnail text-center catalog-default">
                        <% if $BookCopy.CoverImage %>
                        <img src="$BookCopy.CoverImage.PaddedImage(207,303).Watermark.URL" alt="image" class="img-responsive zoom-img" />
                        <% else %>
                        <img alt="" class="img-responsive" src= "librarian/images/book-cover.jpg" />

                        <div class="caption" style="">
                            <h4>$ShortTitle.LimitCharacters(110)</h4>
                        </div>
                        <% end_if %>
                    </div>

                    <div>
                        <h4>$BookCopy.Title</h4>
                        <h5>$BookCopy.Author.Title</h5>
                        <p><%t Librarian.VOLUME_NUMBER "Volume" value=$TheIndex %></p>
                        <% if $NumberOfPages %>
                        <p>$NumberOfPages <%t Librarian.PAGES "Pages" %></p>
                        <% end_if %>
                    </div>		
                </a>
            </div>
            <% end_loop %>
        <% else %>
            <p><%t Librarian.SEARCH_NO_RESULTS 'Sorry, your search query did not return any results.' %></p>
        <% end_if %>
    </div>

    <div class="row">
        <% with $Results %>
            <% include Paginate %>
        <% end_with %>
    </div>
</div>