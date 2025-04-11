<header class="main-nav">
    <nav>
        <div class="main-navbar">
            <div class="left-arrow" id="left-arrow">
                <i data-feather="arrow-left"></i>
            </div>
            <div id="mainnav">
                <ul class="nav-menu custom-scrollbar" style="display: block;">
                    <li class="back-btn">
                        <div class="mobile-back text-end">
                            <span>Back</span>
                            <i class="fa fa-angle-right ps-2" aria-hidden="true"></i>
                        </div>
                    </li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title link-nav active"
                                                   data-active-name="Dashboard"
                                                   href="{{ route('dashboard') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-hexagon">
                                <path
                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <span>Dashboard</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a></li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-box">
                                <path
                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                            <span>Masters</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View States')
                            <li class="">
                                <a href="{{ route('states.index') }}" data-active-name="States" data-original-title="" title="">States</a>
                            </li>
                            @endcan
                            @can('View Districts')
                            <li class="">
                                <a href="{{ route('districts.index') }}" data-active-name="Districts" data-original-title="" title="">Districts</a>
                            </li>
                            @endcan
                            @can('View Cities')
                                <li class="">
                                    <a href="{{ route('cities.index') }}" data-active-name="Cities" data-original-title="" title="">City</a>
                                </li>
                            @endcan
                            @can('View Pincodes')
                            <li class="">
                                <a href="{{ route('pincodes.index') }}" data-active-name="Pincodes" data-original-title="" title="">Pincodes</a>
                            </li>
                            @endcan
                            @can('View Tax')
                            <li class=""><a href="{{ route('taxes.index') }}" data-active-name="Tax" data-original-title="" title="">Tax</a></li>
                            @endcan
                            <li class=""><a href="{{ route('units.index') }}"  data-active-name="Unit-Of-Measurement" data-original-title=""
                                    title="">Unit of Measurement</a></li>
                            @can('View Product Category')
                            <li class="">
                                <a href="{{ route('product_categories.index') }}" data-active-name="Product-Category" data-original-title=""
                                    title="">Product Category</a>
                            </li>
                            @endcan
                            @can('View Product')
                            <li class="">
                                <a href="{{ route('products.index') }}" data-active-name="Product" data-original-title=""
                                    title="">Product</a>
                            </li>
                            @endcan
                            @can('View Warehouse')
                            <li class="">
                                <a href="{{ route('warehouses.index') }}" data-active-name="Warehouses" data-original-title=""
                                    title="">Warehouse</a>
                            </li>
                            @endcan
                            @can('View Contract Type')
                                <li class="">
                                    <a href="{{ route('contract_types.index') }}" data-active-name="Contract-Type" data-original-title=""
                                       title="">Contract Type</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 3h10l6 6v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path>
                                <polyline points="14 3 14 8 19 8"></polyline>
                                <circle cx="12" cy="16" r="3"></circle>
                                <path d="M12 12v2m0 4v2m4-4h-2m-4 0H8"></path>
                            </svg>
                            <span>Manage Projects</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View Amenities')
                                <li class="">
                                    <a href="{{ route('amenities.index') }}" data-active-name="Amenity" data-original-title="" title="">Amenities</a>
                                </li>
                            @endcan
                            @can('View Sites')
                                <li class="">
                                    <a href="{{ route('sites.index') }}" data-active-name="Sites" data-original-title="" title="">Sites</a>
                                </li>
                            @endcan
                            @can('View Projects')
                                <li class="">
                                    <a href="{{ route('projects.index') }}" data-active-name="Projects" data-original-title="" title="">Projects</a>
                                </li>
                            @endcan
                            @can('View Project Tasks')
                                <li class="">
                                    <a href="{{ route('project_tasks.index') }}" data-active-name="Project Tasks" data-original-title="" title="">Project Tasks</a>
                                </li>
                            @endcan
                            @can('View Project Specifications')
                                <li class="">
                                    <a href="{{ route('project_specifications.index') }}" data-active-name="Project-Specifications" data-original-title="" title="">Project Specifications</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-users">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Users &amp; Permissions</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View Roles and Permissions')
                            <li class=""><a
                                    href="{{ route('role.index') }}"  data-active-name="Roles-and-Permissions" data-original-title=""
                                    title="">Roles & Permissions</a></li>
                            @endcan
                            @can('View Users')
                                <li class="">
                                    <a href="{{ route('users.index') }}" data-active-name="Users" data-original-title=""
                                       title="">Manage Users</a>
                                </li>
                            @endcan
                            <!--
                            <li class=""><a
                                    href="http://localhost/VKV-OLD/admin/users-and-permissions/users/"  data-active-name="Users" data-original-title="" title="">Users</a>
                            </li>
                            <li class=""><a
                                    href="http://localhost/VKV-OLD/admin/users-and-permissions/change-password/"  data-active-name="Change-Password" data-original-title=""
                                    title="">Change Password</a></li>
                                    -->
                        </ul>
                    </li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-workspace" viewBox="0 0 16 16">
                            <path d="M4 16s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-5.95a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            <path d="M2 1a2 2 0 0 0-2 2v9.5A1.5 1.5 0 0 0 1.5 14h.653a5.4 5.4 0 0 1 1.066-2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v9h-2.219c.554.654.89 1.373 1.066 2h.653a1.5 1.5 0 0 0 1.5-1.5V3a2 2 0 0 0-2-2z"/>
                          </svg>
                            <span>CRM</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View Lead Source')
                            <li class=""><a
                                    href="{{ route('lead_sources.index') }}"  data-active-name="Lead-Source" data-original-title=""
                                    title="">Lead Source</a></li>
                            @endcan
                            @can('View Lead')
                                <li class="">
                                    <a href="{{ route('leads.index') }}" data-active-name="Lead" data-original-title=""
                                       title="">Leads</a>
                                </li>
                            @endcan
                            @can('View Visitors')
                                <li class="">
                                    <a href="{{ route('visitors.index') }}" data-active-name="Visitor" data-original-title=""
                                       title="">Visitor</a>
                                </li>
                            @endcan
                            <!--
                            <li class=""><a
                                    href="http://localhost/VKV-OLD/admin/users-and-permissions/users/"  data-active-name="Users" data-original-title="" title="">Users</a>
                            </li>
                            <li class=""><a
                                    href="http://localhost/VKV-OLD/admin/users-and-permissions/change-password/"  data-active-name="Change-Password" data-original-title=""
                                    title="">Change Password</a></li>
                                    -->
                        </ul>
                    </li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-person-fill-gear" viewBox="0 0 16 16">
                            <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                          </svg>
                            <span>Labor Management</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View Labor Designations')
                                <li class="">
                                    <a href="{{ route('labor-designations.index') }}" data-active-name="Labor-Designation" data-original-title="" title="">Labor Designation</a>
                                </li>
                            @endcan
                            @can('View Labors')
                                <li class="">
                                    <a href="{{ route('labors.index') }}" data-active-name="Labors" data-original-title="" title="">Labors</a>
                                </li>
                                <li class=""><a href="{{ route('labor.reallocation.view') }}" data-active-name="Labor-Re-Allocation-History" data-original-title="" title="">Labor Re-Allocations</a></li>
                            @endcan
                            @can('View Payrolls')
                                <li class="">
                                    <a href="{{ route('payroll.index') }}" data-active-name="Payroll" data-original-title="" title="">Payrolls</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                          </svg>
                        <span>Transactions</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View Purchase Requests')
                                <li class="">
                                    <a href="{{ route('purchase-requests.index') }}" data-active-name="Purchase-Requests" data-original-title="" title="">Purchase Requests</a>
                                </li>
                            @endcan
                            @can('View Purchase Orders')
                                <li class="">
                                    <a href="{{ route('purchase-orders.index') }}" data-active-name="Purchase-Orders" data-original-title="" title="">Purchase Orders</a>
                                </li>
                            @endcan
                            @can('View Project Stocks')
                                <li class="">
                                    <a href="{{ route('project-stocks.index') }}" data-active-name="Project-Stock-Management" data-original-title="" title="">Project Stock</a>
                                </li>
                                <li class="">
                                    <a href="{{ route('stock-usages.index') }}" data-active-name="Stock-Usage" data-original-title="" title="">Stock Usage Log</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="dropdown CMenus"><a class="nav-link menu-title" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-settings">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path
                                    d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            <span>Settings</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            <li class=""><a href="{{ route('contents.index') }}"
                                            data-active-name="CMS" data-original-title=""
                                            title="">CMS</a></li>
{{--                            <li class=""><a href="http://localhost/VKV-OLD/admin/settings/company/"--}}
{{--                                            data-active-name="Company-Settings" data-original-title=""--}}
{{--                                            title="">Company</a></li>--}}
                        </ul>
                    </li>
                    <li class="dropdown CMenus">
                        <a class="nav-link menu-title" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-headphones">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                <path d="M21 18a3 3 0 0 1-6 0v-6"></path>
                                <path d="M3 18a3 3 0 0 0 6 0v-6"></path>
                            </svg>
                            <span>Support</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View Support Tickets')
                                <li class="">
                                    <a href="{{ route('support_tickets.index') }}" data-active-name="Support-Tickets" title="Support Tickets">
                                        Support Tickets
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="dropdown CMenus">
                        <a class="nav-link menu-title" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-substack" viewBox="0 0 16 16">
                                <path d="M15 3.604H1v1.891h14v-1.89ZM1 7.208V16l7-3.926L15 16V7.208zM15 0H1v1.89h14z"/>
                              </svg>
                            <span>Blog</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            @can('View Blogs')
                                <li class="">
                                    <a href="{{ route('blogs.index') }}" data-active-name="Blog" title="Blogs">
                                        Blogs
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="dropdown CMenus">
                        <a class="nav-link" href="{{route('project_reports.index')}}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-bar-graph" viewBox="0 0 16 16">
                                <path d="M10 13.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-6a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5zm-2.5.5a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zm-3 0a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5z"/>
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                            </svg>
                            <span>Project Reports</span>  
                        </a>
                    </li>
                    <li class="dropdown CMenus" id="btnLogout"><a class="nav-link menu-title link-nav" data-active-name="logout"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-log-in">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            <span>Logout</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow">
                <i data-feather="arrow-right"></i>
            </div>
        </div>
    </nav>
</header>
