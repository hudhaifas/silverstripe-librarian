<div class="thumbnail text-center book-default">
    <% if $CoverImage %>
    <img class="img-responsive" src="$CoverImage.PaddedImage(280, 410).Watermark.URL" />
    <% else %>
    <img alt="" class="img-responsive" src= "librarian/images/book-cover.jpg" />

    <div class="caption" style="">
        <h4>$Up.Title.LimitCharacters(70)</h4>
    </div>
    <% end_if %>
</div>