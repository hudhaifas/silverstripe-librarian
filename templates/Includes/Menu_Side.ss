<div class="col-md-4">
    <% if AuthorsList %>
    <h5><%t Librarian.AUTHORS "Authors" %></h5>
    <ul class="book-categories">
        <% loop AuthorsList.Limit(8) %>
        <li class="cat-item"><a href="$Link">$ShortName</a> <span class="count">($Books.Count)</span></li>
        <% end_loop %>
    </ul>
    <% end_if %>

    <% if CategoriesList %>
    <h5><%t Librarian.CATEGORIES "Categories" %></h5>
    <ul class="book-categories">
        <% loop CategoriesList.Limit(8) %>
        <li class="cat-item"><a href="$Link">$Title</a> <span class="count">($Books.Count)</span></li>
        <% end_loop %>
    </ul>
    <% end_if %>
</div>
