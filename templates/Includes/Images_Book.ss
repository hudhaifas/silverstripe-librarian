<div class="labout volume-img-span">
    <ul id="etalage">
        <li>
            <a href="#">
                <% if $Cover %>
                    <img class="etalage_thumb_image img-responsive" src="$Cover.PaddedImage(300, 400).URL" />
                    <img class="etalage_source_image img-responsive" src="$Cover.URL" title="" />
                <% else %>
                    <img class="etalage_thumb_image img-responsive" src="librarian/images/default-book.jpg"/>
                    <img class="etalage_source_image img-responsive" src="librarian/images/default-book.jpg" title="" />
                <% end_if %>
            </a>
        </li>
    </ul>
    <div class="clearfix"></div>
</div>