<div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation" role="navigation">
            {{-- Dashboard --}}
            <li class="nav-item {{ Request::routeIs('home') ? 'active' : '' }}">
                <a href="{{ route('home') }}" aria-current="{{ Request::routeIs('home') ? 'page' : 'false' }}">
                    <i class="feather icon-home"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            {{-- Customer --}}
            @can('customer.index')
            <li class="nav-item {{ Request::routeIs('customer.*') ? 'active' : '' }}">
                <a href="{{ route('customer.index') }}"
                    aria-current="{{ Request::routeIs('customer.index') ? 'page' : 'false' }}">
                    <i class="feather icon-user"></i>
                    <span class="menu-title">Customer</span>
                </a>
            </li>
            @endcan


            {{-- Suppliers --}}
            @canany(['supplier.create', 'supplier.index'])
            <li class="nav-item has-sub {{ Request::routeIs('supplier.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('supplier.*') ? 'true' : 'false' }}">
                    <i class="fa fa-handshake-o"></i>
                    <span class="menu-title">Suppliers</span>
                </a>
                <ul class="menu-content">
                    @can('supplier.create')
                    <li class="{{ Request::routeIs('supplier.create') ? 'active' : '' }}">
                        <a href="{{ route('supplier.create') }}"
                            aria-current="{{ Request::routeIs('supplier.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Add Supplier</span>
                        </a>
                    </li>
                    @endcan

                    @can('supplier.index')
                    <li class="{{ Request::routeIs('supplier.index') ? 'active' : '' }}">
                        <a href="{{ route('supplier.index') }}"
                            aria-current="{{ Request::routeIs('supplier.create') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Supplier List</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- Spare Parts --}}
            @canany(['spare-parts.create', 'spare-parts.index'])
            <li class="nav-item has-sub {{ Request::routeIs('spare-parts.*') || Request::routeIs('stock-movment.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('spare-parts.*') || Request::routeIs('stock-movment.*') ? 'true' : 'false' }}">
                    <i class="feather icon-settings"></i>
                    <span class="menu-title">Spare Parts</span>
                </a>
                <ul class="menu-content">
                    @can('spare-parts.create')
                    <li class="{{ Request::routeIs('spare-parts.create') ? 'active' : '' }}">
                        <a href="{{ route('spare-parts.create') }}"
                            aria-current="{{ Request::routeIs('spare-parts.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Add Spare Parts</span>
                        </a>
                    </li>
                    @endcan

                    @can('spare-parts.index')
                    <li class="{{ Request::routeIs('spare-parts.index') ? 'active' : '' }}">
                        <a href="{{ route('spare-parts.index') }}"
                            aria-current="{{ Request::routeIs('spare-parts.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Spare Parts List</span>
                        </a>
                    </li>
                    @endcan

                    @can('stock-movment.index')
                    <li class="{{ Request::routeIs('stock-movment.*') ? 'active' : '' }}">
                        <a href="{{ route('stock-movment.index') }}"
                            aria-current="{{ Request::routeIs('stock-movment.*') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Stock Transfer</span>
                        </a>
                    </li>
                    @endcan
                    <!-- <li class="{{ Request::routeIs('spare-parts.invoiceList') ? 'active' : '' }}">
                        <a href="{{ route('spare-parts.invoiceList') }}" aria-current="{{ Request::routeIs('spare-parts.invoiceList') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Invoice List</span>
                        </a>
                    </li> -->
                </ul>
            </li>
            @endcanany


            {{-- Purchase --}}
            @canany(['purchase.purchaseVehicle', 'vehicle.index', 'purchase.create', 'purchase.index'])
            <li class="nav-item has-sub {{ Request::routeIs('purchase.*') || Request::routeIs('vehicle.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('purchase.*') || Request::routeIs('vehicle.*') ? 'true' : 'false' }}">
                    <i class="fa fa-id-card"></i>
                    <span class="menu-title">Purchase</span>
                </a>
                <ul class="menu-content">
                    @can('purchase.purchaseVehicle')
                    <li class="{{ Request::routeIs('purchase.purchaseVehicle') ? 'active' : '' }}">
                        <a href="{{ route('purchase.purchaseVehicle') }}"
                            aria-current="{{ Request::routeIs('purchase.purchaseVehicle') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Purchase Vehicle</span>
                        </a>
                    </li>
                    @endcan

                    @can('vehicle.index')
                    <li class="{{ Request::routeIs('vehicle.index') ? 'active' : '' }}">
                        <a href="{{ route('vehicle.index') }}"
                            aria-current="{{ Request::routeIs('vehicle.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Vehicle List</span>
                        </a>
                    </li>
                    @endcan

                    @can('purchase.create')
                    <li class="{{ Request::routeIs('purchase.create') ? 'active' : '' }}">
                        <a href="{{ route('purchase.create') }}"
                            aria-current="{{ Request::routeIs('purchase.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Spare Parts Purchase</span>
                        </a>
                    </li>
                    @endcan

                    @can('purchase.index')
                    <li class="{{ Request::routeIs('purchase.index') ? 'active' : '' }}">
                        <a href="{{ route('purchase.index') }}"
                            aria-current="{{ Request::routeIs('purchase.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Purchase List</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- Product Search --}}
            @can('productSearch.index')
            <li class="nav-item {{ Request::routeIs('productSearch*') ? 'active' : '' }}">
                <a href="{{ route('productSearch.index') }}"
                    aria-current="{{ Request::routeIs('productSearch.index') ? 'page' : 'false' }}">
                    <i class="feather icon-user"></i>
                    <span class="menu-title">Product Search</span>
                </a>
            </li>
            @endcan


            {{-- Expenses --}}
            @canany(['expense.create', 'expense.index', 'expense-type.create', 'expense-type.index'])
            <li class="nav-item has-sub {{ Request::routeIs('expense.*') || Request::routeIs('expense-type.*') ? 'active open' : '' }}">
                <a href="#"
                    aria-expanded="{{ Request::routeIs('expense.*') || Request::routeIs('expense-type.*') ? 'true' : 'false' }}">
                    <i class="feather icon-credit-card"></i>
                    <span class="menu-title">Expenses</span>
                </a>
                <ul class="menu-content">
                    <li class="has-sub {{ Request::routeIs('expense-type.*') ? 'active open' : '' }}">
                        <a href="{{ route('expense-type.index') }}"
                            aria-expanded="{{ Request::routeIs('expense-type.*') ? 'true' : 'false' }}">
                            <i class="feather icon-layers"></i>
                            <span class="menu-title">Expense Type</span>
                        </a>
                        <ul class="menu-content">
                            @can('expense-type.create')
                            <li class="{{ Request::routeIs('expense-type.create') ? 'active' : '' }}">
                                <a href="{{ route('expense-type.create') }}"
                                    aria-current="{{ Request::routeIs('expense-type.create') ? 'page' : 'false' }}">
                                    <i class="feather icon-plus-circle"></i>
                                    <span class="menu-title">Add Expense Type</span>
                                </a>
                            </li>
                            @endcan

                            @can('expense-type.index')
                            <li class="{{ Request::routeIs('expense-type.index') ? 'active' : '' }}">
                                <a href="{{ route('expense-type.index') }}"
                                    aria-current="{{ Request::routeIs('expense-type.index') ? 'page' : 'false' }}">
                                    <i class="feather icon-list"></i>
                                    <span class="menu-title">Expense Type List</span>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>


                    <li class="has-sub {{ Request::routeIs('expense.*') ? 'active open' : '' }}">
                        <a href="{{ route('expense.index') }}"
                            aria-expanded="{{ Request::routeIs('expense.*') ? 'true' : 'false' }}">
                            <i class="feather icon-credit-card"></i>
                            <span class="menu-title">Expense</span>
                        </a>
                        <ul class="menu-content">
                            @can('expense.create')
                            <li class="{{ Request::routeIs('expense.create') ? 'active' : '' }}">
                                <a href="{{ route('expense.create') }}"
                                    aria-current="{{ Request::routeIs('expense.create') ? 'page' : 'false' }}">
                                    <i class="feather icon-plus-circle"></i>
                                    <span class="menu-title">Add Expense</span>
                                </a>
                            </li>
                            @endcan

                            @can('expense.index')
                            <li class="{{ Request::routeIs('expense.index') ? 'active' : '' }}">
                                <a href="{{ route('expense.index') }}"
                                    aria-current="{{ Request::routeIs('expense.index') ? 'page' : 'false' }}">
                                    <i class="feather icon-list"></i>
                                    <span class="menu-title">Expense List</span>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                </ul>
            </li>
            @endcanany


            {{-- Quote --}}
            @canany(['quotes.create', 'quotes.index'])
            <li class="nav-item has-sub {{ Request::routeIs('quotes.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('quotes.*') ? 'true' : 'false' }}">
                    <i class="feather icon-file-text"></i>
                    <span class="menu-title">Quotes</span>
                </a>
                <ul class="menu-content">
                    @can('quotes.create')
                    <li class="{{ Request::routeIs('quotes.create') ? 'active' : '' }}">
                        <a href="{{ route('quotes.create') }}"
                            aria-current="{{ Request::routeIs('quotes.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Add Quote</span>
                        </a>
                    </li>
                    @endcan

                    @can('quotes.index')
                    <li class="{{ Request::routeIs('quotes.index') ? 'active' : '' }}">
                        <a href="{{ route('quotes.index') }}"
                            aria-current="{{ Request::routeIs('quotes.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Quote List</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- Sales --}}
            @canany(['sales.create', 'sales.index', 'sales.parts-return.index'])
            <li class="nav-item has-sub {{ Request::routeIs('sales.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('sales.*') ? 'true' : 'false' }}">
                    <i class="feather icon-shopping-cart"></i>
                    <span class="menu-title">Sales</span>
                </a>
                <ul class="menu-content">
                    @can('sales.create')
                    <li class="{{ Request::routeIs('sales.create') ? 'active' : '' }}">
                        <a href="{{ route('sales.create') }}"
                            aria-current="{{ Request::routeIs('sales.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Add Sale</span>
                        </a>
                    </li>
                    @endcan

                    @can('sales.index')
                    <li class="{{ Request::routeIs('sales.index') ? 'active' : '' }}">
                        <a href="{{ route('sales.index') }}"
                            aria-current="{{ Request::routeIs('sales.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Sale List</span>
                        </a>
                    </li>
                    @endcan

                    @can('sales.parts-return.index')
                    <li class="{{ Request::routeIs('sales.parts-return.index') ? 'active' : '' }}">
                        <a href="{{ route('sales.parts-return.index') }}"
                            aria-current="{{ Request::routeIs('parts-return.index') ? 'page' : 'false' }}">
                            <i class="fa fa-undo"></i>
                            <span class="menu-title">Parts Return List</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- Reports --}}
            @canany(['report.stock', 'report.profit', 'report.vehicleSale', 'report.dueReport', 'balance-statement.index'])
            <li class="nav-item has-sub {{ Request::routeIs('report.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('report.*') ? 'true' : 'false' }}">
                    <i class="fa fa-file-pdf-o"></i>
                    <span class="menu-title">Reports</span>
                </a>
                <ul class="menu-content">
                    @can('report.stock')
                    <li class="{{ Request::routeIs('report.stock') ? 'active' : '' }}">
                        <a href="{{ route('report.stock') }}"
                            aria-current="{{ Request::routeIs('report.stock') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Stock Report</span>
                        </a>
                    </li>
                    @endcan

                    @can('report.profit')
                    <li class="{{ Request::routeIs('report.profit') ? 'active' : '' }}">
                        <a href="{{ route('report.profit') }}"
                            aria-current="{{ Request::routeIs('report.profit') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Profit Report</span>
                        </a>
                    </li>
                    @endcan

                    @can('report.vehicleSale')
                    <li class="{{ Request::routeIs('report.vehicleSale') ? 'active' : '' }}">
                        <a href="{{ route('report.vehicleSale') }}"
                            aria-current="{{ Request::routeIs('report.vehicleSale') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Vehicle Sale Report</span>
                        </a>
                    </li>
                    @endcan

                    @can('report.dueReport')
                    <li class="{{ Request::routeIs('report.dueReport') ? 'active' : '' }}">
                        <a href="{{ route('report.dueReport') }}"
                            aria-current="{{ Request::routeIs('report.dueReport') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Due Report</span>
                        </a>
                    </li>
                    @endcan

                    @can('balance-statement.index')
                    <li class="nav-item {{ Request::routeIs('balance-statement.*') ? 'active' : '' }}">
                        <a href="{{ route('balance-statement.index') }}"
                            aria-current="{{ Request::routeIs('balance-statement.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Balance Statement</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            <li class="navigation-header">
                <span>System</span>
                <i class=" feather icon-minus" data-toggle="tooltip" data-placement="right" data-original-title="Components"></i>
            </li>


            {{-- Balance --}}
            @canany(['balances.create', 'balances-out.create', 'balances.index'])
            <li class="nav-item has-sub {{ Request::routeIs('balances.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('balances.*') ? 'true' : 'false' }}">
                    <i class="fa fa-money"></i>
                    <span class="menu-title">Balance</span>
                </a>
                <ul class="menu-content">
                    @can('balances.create')
                    <li class="{{ Request::routeIs('balances.create') ? 'active' : '' }}">
                        <a href="{{ route('balances.create') }}"
                            aria-current="{{ Request::routeIs('balances.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Balance In</span>
                        </a>
                    </li>
                    @endcan

                    @can('balances-out.create')
                    <li class="{{ Request::routeIs('balances-out.create') ? 'active' : '' }}">
                        <a href="{{ route('balances-out.create') }}"
                            aria-current="{{ Request::routeIs('balances-out.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Balance Out</span>
                        </a>
                    </li>
                    @endcan

                    @can('balances.index')
                    <li class="{{ Request::routeIs('balances.index') ? 'active' : '' }}">
                        <a href="{{ route('balances.index') }}"
                            aria-current="{{ Request::routeIs('balances.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Balance <br> <span class="pl-2">Transaction List</span></span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- Bank --}}
            @canany(['banks.create', 'banks.index'])
            <li class="nav-item has-sub {{ Request::routeIs('banks.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('banks.*') ? 'true' : 'false' }}">
                    <i class="fa fa-building"></i>
                    <span class="menu-title">Banks</span>
                </a>
                <ul class="menu-content">
                    @can('banks.create')
                    <li class="{{ Request::routeIs('banks.create') ? 'active' : '' }}">
                        <a href="{{ route('banks.create') }}"
                            aria-current="{{ Request::routeIs('banks.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Add Bank</span>
                        </a>
                    </li>
                    @endcan

                    @can('banks.index')
                    <li class="{{ Request::routeIs('banks.index') ? 'active' : '' }}">
                        <a href="{{ route('banks.index') }}"
                            aria-current="{{ Request::routeIs('banks.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Bank List</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- Branch --}}
            @canany(['branch.create', 'branch.index'])
            <li class="nav-item has-sub {{ Request::routeIs('branch.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('branch.*') ? 'true' : 'false' }}">
                    <i class="fa fa-home"></i>
                    <span class="menu-title">Branches</span>
                </a>
                <ul class="menu-content">
                    @can('branch.create')
                    <li class="{{ Request::routeIs('branch.create') ? 'active' : '' }}">
                        <a href="{{ route('branch.create') }}"
                            aria-current="{{ Request::routeIs('branch.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Add Branch</span>
                        </a>
                    </li>
                    @endcan

                    @can('branch.index')
                    <li class="{{ Request::routeIs('branch.index') ? 'active' : '' }}">
                        <a href="{{ route('branch.index') }}"
                            aria-current="{{ Request::routeIs('branch.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Branch List</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- User --}}
            @canany(['roles.index', 'user.create', 'user.index'])
            <li class="nav-item has-sub {{ Request::routeIs('user.*') || Request::routeIs('roles.*') || Request::routeIs('permissions.*') ? 'active open' : '' }}">
                <a href="#" aria-expanded="{{ Request::routeIs('user.*') ? 'true' : 'false' }}">
                    <i class="fa fa-users"></i>
                    <span class="menu-title">System Users</span>
                </a>
                <ul class="menu-content">
                    @can('roles.index')
                    <li class="{{ Request::routeIs('roles*') ? 'active' : '' }}">
                        <a href="{{ route('roles.index') }}"
                            aria-current="{{ Request::routeIs('roles.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">Roles</span>
                        </a>
                    </li>
                    @endcan

                    @can('user.create')
                    <li class="{{ Request::routeIs('user.create') ? 'active' : '' }}">
                        <a href="{{ route('user.create') }}"
                            aria-current="{{ Request::routeIs('user.create') ? 'page' : 'false' }}">
                            <i class="feather icon-plus-circle"></i>
                            <span class="menu-title">Add User</span>
                        </a>
                    </li>
                    @endcan

                    @can('user.index')
                    <li class="{{ Request::routeIs('user.index') ? 'active' : '' }}">
                        <a href="{{ route('user.index') }}"
                            aria-current="{{ Request::routeIs('user.index') ? 'page' : 'false' }}">
                            <i class="feather icon-list"></i>
                            <span class="menu-title">User List</span>
                        </a>
                    </li>
                    @endcan

                </ul>
            </li>
            @endcanany
        </ul>
    </div>
</div>
