<div class="thumbnail text-center volume-default">
    <% if $BookCopy.CoverImage %>
        <img class="img-responsive" src="$BookCopy.CoverImage.PaddedImage(280, 410).Watermark.URL" />
    <% else %>
        <img class="img-responsive" src= "librarian/images/book-cover.jpg" />

        <div class="caption" style="">
            <h4>$BookName.LimitCharacters(110)</h4>
        </div>
    <% end_if %>
</div>