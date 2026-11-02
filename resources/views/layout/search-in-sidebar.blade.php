<div>
    <x-filament::input.wrapper x-show="$store.sidebar.isOpen">
        <x-filament::input
            id="sidebar-search"
            type="search"
            class="searchinsidebar"
            placeholder="{{ __('messages.common.search') }}"
            style="
                background-image: url('{{ asset('images/search.svg') }}');
                background-repeat: no-repeat;
                background-position: 10px center;
                background-size: 20px;
                padding-left: 40px;
            "
        />
    </x-filament::input.wrapper>

    <span id="no-results" style="display: none; padding: 40px;" class="text-yellow">
        {{ __('messages.no_matching_records_found') }}
    </span>

    <script>
        (() => {
            function filterSidebar() {
                const searchInput = document.getElementById('sidebar-search');
                const noResultsDiv = document.getElementById('no-results');

                if (! searchInput || ! noResultsDiv) {
                    return;
                }

                const query = searchInput.value.trim().toLowerCase();
                const sidebarGroups = document.querySelectorAll('.fi-sidebar-group');
                const standaloneItems = document.querySelectorAll('.fi-sidebar-nav > .fi-sidebar-item, .fi-sidebar-nav-groups > .fi-sidebar-item');
                let found = false;

                standaloneItems.forEach((item) => {
                    const button = item.querySelector('.fi-sidebar-item-btn');
                    const isMatch = ! query || button?.textContent.toLowerCase().includes(query);

                    item.style.display = isMatch ? '' : 'none';
                    found = found || Boolean(isMatch);
                });

                sidebarGroups.forEach((group) => {
                    const items = group.querySelectorAll('.fi-sidebar-item');
                    let groupHasVisibleItems = false;

                    items.forEach((item) => {
                        const button = item.querySelector('.fi-sidebar-item-btn');
                        const isMatch = ! query || button?.textContent.toLowerCase().includes(query);

                        item.style.display = isMatch ? '' : 'none';

                        if (isMatch) {
                            groupHasVisibleItems = true;
                            found = true;
                        }
                    });

                    group.style.display = groupHasVisibleItems ? '' : 'none';

                    const groupItems = group.querySelector('.fi-sidebar-group-items');

                    if (groupItems && query) {
                        groupItems.style.display = '';
                    } else if (groupItems) {
                        groupItems.style.display = '';
                    }
                });

                noResultsDiv.style.display = found ? 'none' : 'block';
            }

            function initializeSidebarSearch() {
                const searchInput = document.getElementById('sidebar-search');

                if (! searchInput || searchInput.dataset.sidebarSearchBound === 'true') {
                    return;
                }

                searchInput.dataset.sidebarSearchBound = 'true';
                searchInput.addEventListener('input', filterSidebar);
            }

            document.addEventListener('DOMContentLoaded', initializeSidebarSearch);
            document.addEventListener('livewire:navigated', initializeSidebarSearch);
            document.addEventListener('livewire:navigated', filterSidebar);
        })();
    </script>
</div>
