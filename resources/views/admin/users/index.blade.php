<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('User Management') }}
            </h2>
            <a href="{{ route('users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Terdaftar
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{
                                                    $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' :
                                                    ($user->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800')
                                                }}">
                                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($user->isApproved())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $user->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                @if (!$user->isApproved())
                                                    <form action="{{ route('users.approve', $user) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs"
                                                                onclick="return confirm('Approve user ini?')">
                                                            Approve
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <a href="{{ route('users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                                    Edit
                                                </a>
                                                
                                                @if ($user->id !== auth()->id())
                                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-xs"
                                                                onclick="return confirm('Hapus user ini?')">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
                                            Tidak ada data user.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
