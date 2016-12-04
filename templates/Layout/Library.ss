<% if $LatestBooks.Count %>
<article class="conatiner row">
    <a href="$Link(books)"><h2><%t Librarian.NEW_ARRIVALS 'New Arrivals' %></h2></a>

    <div class="row">
        <% loop $LatestBooks.Limit(4) %>
        <div class="col-md-3">
            <a href="$Link" class="thumbnail text-center">
                <% if $Cover %>
                <img src="$Cover.SetSize(280,410).URL" class="img-responsive related-img" alt="" />
                <% else %>
                <img alt="" class="img-responsive" src= "librarian/images/book-cover.jpg" />

                <div class="caption" style="">
                    <h4>$Title.LimitCharacters(100)</h4>
                </div>
                <% end_if %>
            </a>
        </div>
        <% end_loop %>
    </div>
</article>
<% end_if %>

<% loop PublicCatalogs %>
<article class="conatiner row">
    <a href="$Link"><h2>$Title</h2></a>

    <div class="row">
        <% loop RandomVolumes.Limit(8) %>
        <div class="col-md-3">
            <a href="$Link" class="thumbnail text-center">
                <% if $BookCopy.Book.Cover %>
                <img src="$BookCopy.Book.Cover.SetSize(280,410).URL" class="img-responsive related-img" alt="" />
                <% else %>
                <img alt="" class="img-responsive" src= "librarian/images/book-cover.jpg" />

                <div class="caption" style="">
                    <h4>$ShortTitle.LimitCharacters(100)</h4>
                </div>
                <% end_if %>
            </a>
        </div>
        <% end_loop %>
    </div>
</article>
<% end_loop %>