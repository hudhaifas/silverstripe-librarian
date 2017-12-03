<div class="book col-xs-12 col-sm-6 col-md-12">
    <div class="book-img">
        <a href="$Link" title="$Title">
            <div class="thumbnail text-center related-default">
                <% if $Cover %>
                <img src="$Cover.SetSize(102,149).Watermark.URL" class="img-responsive related-img" alt="" />
                <% else %>
                <img alt="" class="img-responsive" src= "librarian/images/book-cover.jpg" />

                <div class="caption" style="">
                    <h4>$Title.LimitCharacters(100)</h4>
                </div>
                <% end_if %>
            </div>
        </a>
    </div>

    <div class="book-desc">
        <p class="title"><a href="$Link" title="$Title">$Title.LimitCharacters(30)</a></p>
        <p class="author">$Author.Title.LimitCharacters(28)</p>
        <% if $Subject %><p class="line"><%t Librarian.SUBJECT 'Subject' %>: $Subject</p><% end_if %>
        <% if $OriginalPublish %><p class="line"><%t Librarian.ORIGINAL_PUBLISH 'Original Publish' %>: $OriginalPublish</p><% end_if %>
        <% if $Language %><p class="line"><%t Librarian.LANGUAGE 'Language' %>: $Language</p><% end_if %>
    </div>
</div>