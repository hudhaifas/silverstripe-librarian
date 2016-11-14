<% with Copy %>
<div class="col-md-9 single_top">
    <div class="single_left">
        <% include Images_Book %>

        <div class="cont1 volume-desc-span">
            <h1>$Title</h1>
            <p class="information"><%t Librarian.STATUS "Status" %>: <span class="color">$Available <%t Librarian.COPYIES "Copy(ies)" %></span></p>
            <p class="information"><%t Librarian.PUBLISHER "Publisher" %>: <span class="color">$Publisher.Name</span></p>
            <p class="information"><%t Librarian.LENGTH "Length" %>: <span class="color">$NumberOfPages <%t Librarian.PAGES "Pages" %></span></p>

            <div class="price_single"></div>

            <% if $Overview %>
            <h2 class="quick"><%t Librarian.QUICK_OVERVIEW "Quick Overview" %>:</h2>
            <p class="quick_desc">$Overview.Summary</p>
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
    <% if $Book.Related %>
    <h3 class="m_1"><%t Librarian.RELATED_BOOKS "Related Books" %></h3>
    <% loop $Book.RandomCategories. %>
        <% include RelatedBook %>
    <% end_loop %>
    <% end_if %>
</div>
<div class="clearfix"> </div>
<% end_with %>