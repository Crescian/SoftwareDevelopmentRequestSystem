.stat-card {
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    height: 100%;
}

.stat-card .num {
    font-weight: 900;
    font-size: 30px;
    line-height: 1;
    letter-spacing: -.5px;
}

.stat-card .label {
    font-size: 13px;
    font-weight: 700;
    margin-top: 8px;
}

.stat-card .hint {
    font-size: 11px;
    color: var(--tm);
    margin-top: 2px;
}

.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}

.stat-icon.blue   { background: var(--acl); color: var(--nl); }
.stat-icon.amber  { background: #fff4d6; color: #d9880c; }
.stat-icon.red    { background: #fde8e8; color: #e5484d; }
.stat-icon.green  { background: #e3f7ea; color: #1b9a4b; }
.stat-icon.purple { background: #f0e7fd; color: #7c3aed; }

.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px 12px;
}

.section-head h2 {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
}

.section-head a {
    font-size: 12px;
    font-weight: 700;
    color: var(--nl);
    text-decoration: none;
}

.table-requests {
    margin: 0;
    font-size: 13px;
}

.table-requests thead th {
    font-size: 11px;
    font-weight: 700;
    color: var(--tm);
    text-transform: uppercase;
    letter-spacing: .4px;
    border-bottom: 1px solid var(--bd);
    padding: 10px 22px;
    background: #fafbfd;
    white-space: nowrap;
}

.table-requests tbody td {
    padding: 13px 22px;
    vertical-align: middle;
    border-bottom: 1px solid #eef1f5;
}

.table-requests tbody tr:last-child td {
    border-bottom: none;
}

.table-requests .ref {
    font-weight: 700;
    color: var(--tm);
    white-space: nowrap;
}

.table-requests .title {
    font-weight: 700;
    color: var(--nd);
}

.table-requests .view-link {
    font-size: 12px;
    font-weight: 700;
    color: var(--nl);
    text-decoration: none;
}

.empty-state {
    padding: 40px 22px;
    text-align: center;
    color: var(--tm);
    font-size: 13px;
}

.empty-state i {
    font-size: 32px;
    color: var(--bd);
    display: block;
    margin-bottom: 8px;
}
