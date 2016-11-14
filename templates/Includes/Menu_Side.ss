<div class="col-md-3">
    <% if AuthorsList %>
    <h3><%t Librarian.AUTHORS "Authors" %></h3>
    <ul class="book-categories">
        <% loop AuthorsList.Limit(8) %>
        <li class="cat-item"><a href="$Link">$ShortName</a> <span class="count">($Books.Count)</span></li>
        <% end_loop %>
    </ul>
    <% end_if %>

    <% if CategoriesList %>
    <h3><%t Librarian.CATEGORIES "Categories" %></h3>
    <ul class="book-categories">
        <% loop CategoriesList.Limit(8) %>
        <li class="cat-item"><a href="$Link">$Title</a> <span class="count">($Books.Count)</span></li>
        <% end_loop %>
    </ul>
    <% end_if %>
</div>
