<div class="col-md-12">
    <div class="books-toolbar">
        <% with $PaginateList %>
        <% include Paginate %>
        <% end_with %>
        <div class="clearfix"></div>		
    </div>

    <% loop $PaginateList %>
    <div class="brand_box">
        <div class="left-side col-xs-12 col-sm-2">
            <% if $Photo %>
            <img src="$Photo.PaddedImage(146,293).URL" alt="image" class="img-responsive zoom-img" />
            <% else %>
            <img src="librarian/images/default-author.jpg" alt="image" class="img-responsive zoom-img" />
            <% end_if %>
        </div>

        <div class="middle-side col-xs-12 col-sm-7">
            <h3><a href="$Link">$ShortName</a></h3>
            <h5><a>$FullName</a></h5>
            $Biography.Summary
        </div>

        <div class="right-side col-xs-12 col-sm-3">
            <p>
                <a>
                    <% if $Books.Count == 0 %>
                        <%t Librarian.NO_BOOKS "No Books" %>
                    <% else_if $Books.Count == 1 %>
                        <%t Librarian.ONE_BOOK "One Book" %>
                    <% else_if $Books.Count == 2 %>
                        <%t Librarian.TWO_BOOKS "Two Books" %>
                    <% else %>
                        <%t Librarian.BOOKS_COUNT "{value} Books" value=$Books.Count %>
                    <% end_if %>
                </a>
            </p>
            <a href="$Link" title="Online Reservation" class="btn btn1 btn-primary btn-normal btn-inline " target="_self"><%t Librarian.VIEW_BOOKS "View Books" %></a>     
        </div>
        <div class="clearfix"> </div>
    </div>
    <% end_loop %>
</div>

<div class="clearfix"> </div>