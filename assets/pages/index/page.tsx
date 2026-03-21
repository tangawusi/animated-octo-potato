import React, { FormEvent, useEffect, useMemo, useState } from 'react';

type UserShape = {
    id: number;
    email: string;
    verified: boolean;
};

type NoteShape = {
    id: number;
    title: string;
    content: string;
    category: string;
    status: string;
    createdAt: string | null;
    updatedAt: string | null;
};

type NotesResponse = {
    items: NoteShape[];
    filters: {
        statuses: string[];
        categories: string[];
    };
};

type ApiResponse = {
    message?: string;
    user?: UserShape | null;
    authenticated?: boolean;
    item?: NoteShape;
};

type FiltersState = {
    q: string;
    status: string;
    category: string;
};

type NoteFormState = {
    title: string;
    content: string;
    category: string;
    status: string;
};

const defaultFilters: FiltersState = {
    q: '',
    status: '',
    category: '',
};

const defaultNoteForm: NoteFormState = {
    title: '',
    content: '',
    category: '',
    status: 'new',
};

const confirmationMessages: Record<string, string> = {
    success: 'Account confirmed. You can now log in.',
    invalid: 'That confirmation link is invalid.',
    expired: 'That confirmation link has expired. Register again to generate a fresh link.',
};

async function apiRequest<T>(path: string, options: RequestInit = {}): Promise<T> {
    const headers: HeadersInit = {
        'Content-Type': 'application/json',
        ...(options.headers ?? {}),
    };

    const response = await fetch(path, {
        credentials: 'same-origin',
        ...options,
        headers,
    });

    if (response.status === 204) {
        return {} as T;
    }

    const payload = (await response.json()) as T & { message?: string };

    if (!response.ok) {
        throw new Error(payload.message ?? 'Request failed.');
    }

    return payload;
}

const IndexPage = () => {
    const [user, setUser] = useState<UserShape | null>(null);
    const [registrationEmail, setRegistrationEmail] = useState('');
    const [registrationPassword, setRegistrationPassword] = useState('');
    const [loginEmail, setLoginEmail] = useState('');
    const [loginPassword, setLoginPassword] = useState('');
    const [message, setMessage] = useState<string>('');
    const [notes, setNotes] = useState<NoteShape[]>([]);
    const [filters, setFilters] = useState<FiltersState>(defaultFilters);
    const [availableStatuses, setAvailableStatuses] = useState<string[]>(['new', 'todo', 'done']);
    const [availableCategories, setAvailableCategories] = useState<string[]>([]);
    const [noteForm, setNoteForm] = useState<NoteFormState>(defaultNoteForm);
    const [isBusy, setIsBusy] = useState(false);

    const activeConfirmationMessage = useMemo(() => {
        const params = new URLSearchParams(window.location.search);
        const confirmation = params.get('confirmation');

        if (!confirmation) {
            return '';
        }

        return confirmationMessages[confirmation] ?? '';
    }, []);

    useEffect(() => {
        const bootstrap = async () => {
            try {
                const payload = await apiRequest<ApiResponse>('/api/me', {
                    method: 'GET',
                });

                if (payload.authenticated && payload.user) {
                    setUser(payload.user);
                }
            } catch (error) {
                setUser(null);
            }
        };

        void bootstrap();
    }, []);

    useEffect(() => {
        if (!user) {
            setNotes([]);
            setAvailableCategories([]);
            return;
        }

        void loadNotes();
    }, [user]);

    const loadNotes = async (nextFilters: FiltersState = filters) => {
        const query = new URLSearchParams();

        if (nextFilters.q.trim() !== '') {
            query.set('q', nextFilters.q.trim());
        }

        if (nextFilters.status.trim() !== '') {
            query.set('status', nextFilters.status.trim());
        }

        if (nextFilters.category.trim() !== '') {
            query.set('category', nextFilters.category.trim());
        }

        const payload = await apiRequest<NotesResponse>(`/api/notes?${query.toString()}`, {
            method: 'GET',
        });

        setNotes(payload.items);
        setAvailableStatuses(payload.filters.statuses);
        setAvailableCategories(payload.filters.categories);
    };

    const handleRegister = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsBusy(true);
        setMessage('');

        try {
            const payload = await apiRequest<ApiResponse>('/api/register', {
                method: 'POST',
                body: JSON.stringify({
                    email: registrationEmail,
                    password: registrationPassword,
                }),
            });

            setRegistrationEmail('');
            setRegistrationPassword('');
            setMessage(payload.message ?? 'Registration completed.');
        } catch (error) {
            setMessage(error instanceof Error ? error.message : 'Registration failed.');
        } finally {
            setIsBusy(false);
        }
    };

    const handleLogin = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsBusy(true);
        setMessage('');

        try {
            const payload = await apiRequest<ApiResponse>('/api/login', {
                method: 'POST',
                body: JSON.stringify({
                    email: loginEmail,
                    password: loginPassword,
                }),
            });

            setLoginEmail('');
            setLoginPassword('');
            setUser(payload.user ?? null);
            setMessage(payload.message ?? 'Login completed.');
        } catch (error) {
            setMessage(error instanceof Error ? error.message : 'Login failed.');
        } finally {
            setIsBusy(false);
        }
    };

    const handleLogout = async () => {
        setIsBusy(true);
        setMessage('');

        try {
            await apiRequest<ApiResponse>('/api/logout', {
                method: 'POST',
                body: JSON.stringify({}),
            });

            setUser(null);
            setMessage('Logged out.');
        } catch (error) {
            setMessage(error instanceof Error ? error.message : 'Logout failed.');
        } finally {
            setIsBusy(false);
        }
    };

    const handleCreateNote = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsBusy(true);
        setMessage('');

        try {
            const payload = await apiRequest<ApiResponse>('/api/notes', {
                method: 'POST',
                body: JSON.stringify(noteForm),
            });

            setNoteForm(defaultNoteForm);
            setMessage(payload.message ?? 'Note created.');
            await loadNotes();
        } catch (error) {
            setMessage(error instanceof Error ? error.message : 'Unable to create note.');
        } finally {
            setIsBusy(false);
        }
    };

    const handleApplyFilters = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsBusy(true);
        setMessage('');

        try {
            await loadNotes(filters);
        } catch (error) {
            setMessage(error instanceof Error ? error.message : 'Unable to load notes.');
        } finally {
            setIsBusy(false);
        }
    };

    return (
        <main className="page-shell">
            <section className="hero-card">
                <div>
                    <p className="eyebrow">AJ Enterprises Group Challenge</p>
                    <h1>Symfony + React notes service</h1>
                    <p className="hero-copy">
                        Registration writes a confirmation email into <code>var/emails</code>. Notes support search by title or
                        content, plus status and category filters.
                    </p>
                </div>

                {user ? (
                    <div className="auth-pill">
                        <span>{user.email}</span>
                        <button type="button" className="secondary-button" onClick={handleLogout} disabled={isBusy}>
                            Logout
                        </button>
                    </div>
                ) : (
                    <div className="auth-pill">
                        <span>Logged out</span>
                    </div>
                )}
            </section>

            {(activeConfirmationMessage || message) && (
                <section className="message-card">
                    {activeConfirmationMessage && <p>{activeConfirmationMessage}</p>}
                    {message && <p>{message}</p>}
                </section>
            )}

            {!user && (
                <section className="two-column-grid">
                    <form className="panel" onSubmit={handleRegister}>
                        <h2>Create account</h2>
                        <label>
                            <span>Email</span>
                            <input
                                type="email"
                                value={registrationEmail}
                                onChange={(event) => setRegistrationEmail(event.target.value)}
                                required
                            />
                        </label>
                        <label>
                            <span>Password</span>
                            <input
                                type="password"
                                value={registrationPassword}
                                onChange={(event) => setRegistrationPassword(event.target.value)}
                                minLength={8}
                                required
                            />
                        </label>
                        <button type="submit" disabled={isBusy}>
                            Register
                        </button>
                    </form>

                    <form className="panel" onSubmit={handleLogin}>
                        <h2>Login</h2>
                        <label>
                            <span>Email</span>
                            <input
                                type="email"
                                value={loginEmail}
                                onChange={(event) => setLoginEmail(event.target.value)}
                                required
                            />
                        </label>
                        <label>
                            <span>Password</span>
                            <input
                                type="password"
                                value={loginPassword}
                                onChange={(event) => setLoginPassword(event.target.value)}
                                required
                            />
                        </label>
                        <button type="submit" disabled={isBusy}>
                            Login
                        </button>
                    </form>
                </section>
            )}

            {user && (
                <>
                    <section className="two-column-grid">
                        <form className="panel" onSubmit={handleCreateNote}>
                            <h2>Create note</h2>
                            <label>
                                <span>Title</span>
                                <input
                                    type="text"
                                    value={noteForm.title}
                                    onChange={(event) => setNoteForm({ ...noteForm, title: event.target.value })}
                                    required
                                />
                            </label>
                            <label>
                                <span>Content</span>
                                <textarea
                                    value={noteForm.content}
                                    onChange={(event) => setNoteForm({ ...noteForm, content: event.target.value })}
                                    rows={6}
                                    required
                                />
                            </label>
                            <label>
                                <span>Category</span>
                                <input
                                    type="text"
                                    value={noteForm.category}
                                    onChange={(event) => setNoteForm({ ...noteForm, category: event.target.value })}
                                    required
                                />
                            </label>
                            <label>
                                <span>Status</span>
                                <select
                                    value={noteForm.status}
                                    onChange={(event) => setNoteForm({ ...noteForm, status: event.target.value })}
                                >
                                    {availableStatuses.map((status) => (
                                        <option key={status} value={status}>
                                            {status}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <button type="submit" disabled={isBusy}>
                                Save note
                            </button>
                        </form>

                        <form className="panel" onSubmit={handleApplyFilters}>
                            <h2>Filter notes</h2>
                            <label>
                                <span>Search title or content</span>
                                <input
                                    type="text"
                                    value={filters.q}
                                    onChange={(event) => setFilters({ ...filters, q: event.target.value })}
                                />
                            </label>
                            <label>
                                <span>Status</span>
                                <select
                                    value={filters.status}
                                    onChange={(event) => setFilters({ ...filters, status: event.target.value })}
                                >
                                    <option value="">All statuses</option>
                                    {availableStatuses.map((status) => (
                                        <option key={status} value={status}>
                                            {status}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label>
                                <span>Category</span>
                                <select
                                    value={filters.category}
                                    onChange={(event) => setFilters({ ...filters, category: event.target.value })}
                                >
                                    <option value="">All categories</option>
                                    {availableCategories.map((category) => (
                                        <option key={category} value={category}>
                                            {category}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <button type="submit" disabled={isBusy}>
                                Apply filters
                            </button>
                        </form>
                    </section>

                    <section className="panel note-list-panel">
                        <div className="list-header">
                            <h2>Your notes</h2>
                            <button type="button" className="secondary-button" onClick={() => void loadNotes()} disabled={isBusy}>
                                Refresh
                            </button>
                        </div>

                        {notes.length === 0 ? (
                            <p className="empty-state">No notes matched the current filters.</p>
                        ) : (
                            <div className="notes-grid">
                                {notes.map((note) => (
                                    <article className="note-card" key={note.id}>
                                        <div className="note-meta">
                                            <span className="tag">{note.status}</span>
                                            <span className="tag">{note.category}</span>
                                        </div>
                                        <h3>{note.title}</h3>
                                        <p>{note.content}</p>
                                        <small>Updated: {note.updatedAt ? new Date(note.updatedAt).toLocaleString() : 'n/a'}</small>
                                    </article>
                                ))}
                            </div>
                        )}
                    </section>
                </>
            )}
        </main>
    );
};

export { IndexPage };
