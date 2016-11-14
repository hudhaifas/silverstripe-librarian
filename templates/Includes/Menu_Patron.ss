<% with $PatronID %>
lhlh $PatronID
<% end_with %>

$Title
$PatronID
<div class="col-md-2">
    <h3><%t Librarian.PATRON "Patron" %></h3>
    <ul class="book-categories color">
        <li class="cat-item">
            <a href="{$Link(loans/overdue/$PatronID)}"><%t Librarian.LOAN_OVERDUE 'Overdue' %></a> <span class="count">($OverdueLoansList.Count)</span>
        </li>
        <li class="cat-item">
            <a href="{$Link(lend)}"><%t Librarian.LEND_BOOK 'Lend Book' %></a> <span class="count"></span>
        </li>
        <li class="cat-item">
            <a href="{$Link(loans/return/$PatronID)}"><%t Librarian.RETURN_BOOK 'Return Book' %></a> <span class="count">($LoansList.Count)</span>
        </li>
        <li class="cat-item">
            <a href="{$Link(loans/archive/$PatronID)}"><%t Librarian.LOAN_ARCHIVE 'Archive' %></a> <span class="count"></span>
        </li>
    </ul>
</div>
