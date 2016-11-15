<% include Menu_Side %>

<article class="col-md-8">

    <div class="row">
        $SearchBook
    </div>

    <% if $Query %>
    <div class="row">
        <%t Librarian.SEARCH_QUERY 'You searched for &quot;{value}&quot;' value=$Query %>
    </div>
    <% end_if %>

    <% if $Author %>
    <div class="row">
        <% if $Author.Biography %>
        <a data-toggle="collapse" data-target="#biography">$Author.FullName</a>
        <div id="biography" class="collapse justify">
            $Author.Biography
        </div>
        <% else %>
        $Author.FullName
        <% end_if %>
    </div>
    <% end_if %>
    
    <div class="row">
        <% if $Results %>
            <% loop $Results %>
            <div class="col-md-4">
                <a href="$Link">
                    <div class="thumbnail text-center books-default">
                        <% if $Cover %>
                        <img src="$Cover.PaddedImage(207,303).URL" alt="image" class="img-responsive zoom-img" />
                        <% else %>
                        <img alt="" class="img-responsive" src= "librarian/images/book-cover.jpg" />

                        <div class="caption" style="">
                            <h4>$Title.LimitCharacters(110)</h4>
                        </div>
                        <% end_if %>
                    </div>

                    <div>
                        <h5>$Title.LimitCharacters(70)</h5>
                        <h6>$Author.Title.LimitCharacters(70)</h6>
                        <p>$Subject.LimitCharacters(27)</p>
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
</article>