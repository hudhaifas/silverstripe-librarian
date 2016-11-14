<div class="col-md-2">
    <h3><%t Librarian.LIBRARIAN "Librarian" %></h3>
    <ul class="book-categories color">
        <li class="cat-item">
            <a href="{$Link(loans/overdue)}"><%t Librarian.LOAN_OVERDUE 'Overdue' %></a> <span class="count">($OverdueLoansList.Count)</span>
        </li>
        <li class="cat-item">
            <a href="{$Link(lend)}"><%t Librarian.LEND_BOOK 'Lend Book' %></a> <span class="count"></span>
        </li>
        <li class="cat-item">
            <a href="{$Link(loans/return)}"><%t Librarian.RETURN_BOOK 'Return Book' %></a> <span class="count">($LoansList.Count)</span>
        </li>
        <li class="cat-item">
            <a href="{$Link(loans/archive)}"><%t Librarian.LOAN_ARCHIVE 'Archive' %></a> <span class="count"></span>
        </li>
    </ul>
</div>
