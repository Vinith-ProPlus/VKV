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
                            @can('View Labor Designations')
                                <li class="">
                                    <a href="{{ route('labor-designations.index') }}" data-active-name="Labor-Designation" data-original-title="" title="">Labor Designation</a>
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
                            @can('View Labors')
                                <li class="">
                                    <a href="{{ route('labors.index') }}" data-active-name="Labors" data-original-title="" title="">Labors</a>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-users">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-settings">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path
                                    d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            <span>Re-Allocations</span>
                            <div class="according-menu"><i class="fa fa-angle-double-right"></i></div>
                        </a>
                        <ul class="nav-submenu menu-content" style="display: none;">
                            <li class=""><a href="{{ route('labor.reallocation.view') }}"
                                            data-active-name="Labor-Re-Allocation-History" data-original-title=""
                                            title="">Labor Re-Allocations</a></li>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-headphones">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                <path d="M21 18a3 3 0 0 1-6 0v-6"></path>
                                <path d="M3 18a3 3 0 0 0 6 0v-6"></path>
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
