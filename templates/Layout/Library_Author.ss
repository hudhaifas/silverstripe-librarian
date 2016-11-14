<% with Author %>
<div class="col-md-9 single_top">
    <div class="single_left">
        <% include Images_Book %>

        <div class="cont1 volume-desc-span">
            <h1>$ShortName</h1>
            <h5>$FullName</h5>
            <p class="information">
                <span class="color">
                    <% if $Books.Count == 0 %>
                        <%t Librarian.NO_BOOKS "No Books" %>
                    <% else_if Books.Count == 1 %>
                        <%t Librarian.ONE_BOOK "One Book" %>
                    <% else %>
                        <%t Librarian.BOOKS_COUNT "{value} Books" value=$Books.Count %>
                    <% end_if %>
                </span>
            </p>

            <div class="price_single"></div>

            <% if $Biography %>
            <h2 class="quick"><%t Librarian.ABOUT "About" %>:</h2>
            <p class="quick_desc">$Biography</p>
            <div class="clearfix"></div>
            <% end_if %>

            <% include ShareButtons %>
            <div class="clearfix"></div>
        </div>

        <div class="clearfix"> </div>
    </div>

    <div class="sap_tabs">
        <div id="horizontalTab" style="display: block; width: 100%; margin: 0px;">
            <ul class="resp-tabs-list">
                <% if $Overview %>
                <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><span><%t Librarian.BOOK_OVERVIEW "Book Overview" %></span></li>
                <% end_if %>

                <% if $Reviews %>
                <li class="resp-tab-item" aria-controls="tab_item-1" role="tab"><span>Reviews</span></li>
                <% end_if %>
                <div class="clear"></div>
            </ul>

            <div class="resp-tabs-container">
                <% if $Overview %>
                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-0">
                    <div class="facts">
                        <ul class="tab_list">
                            <li>$Overview</li>
                        </ul>
                    </div>
                </div>
                <% end_if %>

                <% if $Reviews %>
                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-1">
                    <ul class="tab_list">
                        <li><a>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat</a></li>
                    </ul>
                </div>
                <% end_if %>
            </div>
        </div>
    </div>
</div>

<div class="col-md-3">
    <% if BookCopies %>
    <h3 class="m_1"><%t Librarian.BOOK_COPIES "Copies" %></h3>
    <% loop BookCopies %>
    <% include RelatedBook %>
    <% end_loop %>
    <% end_if %>
</div>
<div class="clearfix"> </div>
<% end_with %>