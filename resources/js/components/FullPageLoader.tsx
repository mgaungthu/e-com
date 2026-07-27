export default function FullPageLoader() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-100">
      <div className="flex flex-col items-center gap-3">
        <div className="h-10 w-10 animate-spin rounded-full border-4 border-slate-300 border-t-blue-600" />

        <p className="text-sm text-slate-500">
          Loading...
        </p>
      </div>
    </div>
  );
}