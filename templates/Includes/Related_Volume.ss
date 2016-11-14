<ul class="book col-xs-12 col-sm-6 col-md-12">
    <li class="book-img">
        <a href="$Link" title="$BookName">
            <div class="thumbnail text-center related-default">
                <% if $BookCopy.Book.Cover %>
                    <img src="$BookCopy.Book.Cover.SetSize(102,149).URL" class="img-responsive related-img" alt="" />
                <% else %>
                    <img alt="" class="img-responsive" src= "librarian/images/book-cover.jpg" />

                    <div class="caption" style="">
                        <h4>$Title.LimitCharacters(100)</h4>
                    </div>
                <% end_if %>
            </div>
        </a>
    </li>

    <li class="book-desc">
        <p class="title"><a href="$Link" title="$BookName">$BookName.LimitCharacters(30)</a></p>
        <p class="author">$Author.Title</p>
        <p class="line"><%t Librarian.VOLUME_NUMBER "Volume Number {value}" value=$TheIndex %></p>
        <% if $Publisher && $Publisher.Name %>
            <p class="line">$Publisher.Name</p>
        <% end_if %>
        <% if $Length %><p class="line"><%t Librarian.LENGTH "Length" %>: $Length</p><% end_if %>
    </li>

    <div class="clearfix"> </div>
</ul>