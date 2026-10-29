// columns: [{ key, label, width?, render?(row) }]
export default function Table({ columns, rows, rowKey = 'id' }) {
  return (
    <div className="table-wrap">
      <table className="table">
        <thead>
          <tr>{columns.map((c) => <th key={c.key} style={{ width: c.width }}>{c.label}</th>)}</tr>
        </thead>
        <tbody>
          {rows.map((row, i) => (
            <tr key={row?.[rowKey] ?? i}>
              {columns.map((c, j) => <td key={`${c.key}-${j}`}>{c.render ? c.render(row) : row[c.key]}</td>)}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
